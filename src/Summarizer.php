<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize;

use Carbon\Carbon;
use Datlechin\Ai\Ai;
use Datlechin\Ai\Exception\NotConfiguredException;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Response;
use Flarum\Discussion\Discussion;
use Flarum\Extension\ExtensionManager;
use Flarum\Foundation\ValidationException;
use Flarum\Http\SlugManager;
use Flarum\Http\UrlGenerator;
use Flarum\Locale\LocaleManager;
use Flarum\Locale\TranslatorInterface;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Builder;

class Summarizer
{
    public const int HEAD_POSTS = 5;

    public const int TAIL_POSTS = 5;

    public const int MAX_POST_LENGTH = 4000;

    // Room for reasoning models, which spend output tokens before they write.
    public const int MAX_OUTPUT_TOKENS = 2048;

    public function __construct(
        protected Ai $ai,
        protected SettingsRepositoryInterface $settings,
        protected ExtensionManager $extensions,
        protected LocaleManager $locales,
        protected TranslatorInterface $translator,
        protected UrlGenerator $url,
        protected SlugManager $slugs,
        protected Repository $cache,
    ) {
    }

    public function minPosts(): int
    {
        return max(1, (int) $this->settings->get('datlechin-ai-summarize.min_posts'));
    }

    public function canGenerate(Discussion $discussion): bool
    {
        try {
            $this->assertCanGenerate($discussion);
        } catch (ValidationException|NotConfiguredException) {
            return false;
        }

        return true;
    }

    public function assertCanGenerate(Discussion $discussion): void
    {
        if (! $this->ai->isConfigured()) {
            throw new NotConfiguredException('No AI provider is set up yet.');
        }

        if ($this->comments($discussion->id)->count() < $this->minPosts()) {
            throw new ValidationException(['summary' => $this->translator->trans('datlechin-ai-summarize.api.too_short_message', ['count' => $this->minPosts()])]);
        }

        if ($discussion->summary && $this->newReplyCount($discussion->summary) === 0) {
            throw new ValidationException(['summary' => $this->translator->trans('datlechin-ai-summarize.api.up_to_date_message')]);
        }
    }

    public function newReplyCount(Summary $summary): int
    {
        return $this->comments($summary->discussion_id)
            ->where('number', '>', $summary->last_post_number)
            ->count();
    }

    // Another request or the scheduler may be writing a summary of the same discussion.
    public function acquire(Discussion $discussion): bool
    {
        return $this->cache->add($this->lockKey($discussion), true, 300);
    }

    public function release(Discussion $discussion): void
    {
        $this->cache->forget($this->lockKey($discussion));
    }

    public function posts(Discussion $discussion): array
    {
        $max = max(self::HEAD_POSTS + self::TAIL_POSTS, (int) $this->settings->get('datlechin-ai-summarize.max_posts'));

        if ($this->comments($discussion->id)->count() <= $max) {
            return $this->comments($discussion->id)->with('user')->orderBy('number')->get()->all();
        }

        $head = $this->comments($discussion->id)->with('user')->orderBy('number')->limit(self::HEAD_POSTS)->get();
        $tail = $this->comments($discussion->id)->with('user')->orderByDesc('number')->limit(self::TAIL_POSTS)->get();

        $middle = $this->comments($discussion->id)
            ->with('user')
            ->whereNotIn('id', [...$head->modelKeys(), ...$tail->modelKeys()])
            ->when($this->extensions->isEnabled('flarum-likes'), fn (Builder $query) => $query->withCount('likes')->orderByDesc('likes_count'))
            ->orderByDesc('number')
            ->limit($max - self::HEAD_POSTS - self::TAIL_POSTS)
            ->get();

        return $head->merge($middle)->merge($tail)->sortBy('number')->values()->all();
    }

    public function prompt(Discussion $discussion, array $posts): Prompt
    {
        $locale = (string) $this->settings->get('default_locale');
        $language = $this->locales->getLocales()[$locale] ?? $locale;
        $custom = trim((string) $this->settings->get('datlechin-ai-summarize.instructions'));

        $instructions = [
            "You summarize discussions on {$this->settings->get('forum_title')}, a forum.",
            "Write the summary in $language ($locale), whatever language the posts are in.",
            '',
            'Rules:',
            '- Use only what the posts say. Never add facts, opinions, people or posts that are not there.',
            '- Refer to people as @username, exactly as their post header shows it.',
            '- Cite the posts each point comes from by their numbers, like [#3] or [#3, #8].',
            '- Cover the topic or question, the main answers and positions, and any outcome or open question.',
            '- Stay neutral. Keep it under 200 words. No title, no preamble, no closing remarks.',
            $this->extensions->isEnabled('flarum-markdown')
                ? '- Use Markdown: a short paragraph, then a bullet list when there are several points.'
                : '- Write plain text, without Markdown.',
        ];

        if ($custom !== '') {
            array_push($instructions, '', "The forum's admins add these instructions:", $custom);
        }

        $input = ["Title: $discussion->title"];

        foreach ($posts as $post) {
            $input[] = "\n[#$post->number] @".($post->user->username ?? '[deleted]').":\n".$this->excerpt((string) $post->content);
        }

        return Prompt::make(implode("\n", $instructions), implode("\n", $input), self::MAX_OUTPUT_TOKENS);
    }

    public function generate(Discussion $discussion, ?User $actor = null): Summary
    {
        $posts = $this->posts($discussion);

        return $this->save($discussion, $posts, $this->ai->generate($this->prompt($discussion, $posts)), $actor);
    }

    public function save(Discussion $discussion, array $posts, Response $response, ?User $actor = null): Summary
    {
        $summary = Summary::query()->where('discussion_id', $discussion->id)->first() ?? new Summary();

        $summary->discussion_id = $discussion->id;
        $summary->user_id = $actor?->id;
        $summary->model = mb_substr($response->model, 0, 100);
        $summary->post_ids = array_map(fn (Post $post) => $post->id, $posts);
        $summary->last_post_number = max(array_map(fn (Post $post) => $post->number, $posts));
        $summary->created_at = Carbon::now();
        $summary->setContentAttribute($this->link($response->text, $discussion, $posts), $actor);
        $summary->save();

        $discussion->setRelation('summary', $summary);

        return $summary;
    }

    protected function link(string $text, Discussion $discussion, array $posts): string
    {
        $numbers = array_map(fn (Post $post) => $post->number, $posts);
        $authors = array_filter(array_map(fn (Post $post) => mb_strtolower((string) $post->user?->username), $posts));
        $markdown = $this->extensions->isEnabled('flarum-markdown');
        $slug = $this->slugs->forResource(Discussion::class)->toSlug($discussion);

        // Only posts that were in the input can be cited; a citation of any other is dropped with its leading space.
        $text = preg_replace_callback('/(\s*)\[\s*#\d+(?:\s*,\s*#?\d+)*\s*\]/', function (array $match) use ($numbers, $markdown, $slug) {
            preg_match_all('/\d+/', $match[0], $found);
            $cited = array_intersect(array_map('intval', $found[0]), $numbers);

            if (! $cited) {
                return '';
            }

            return $match[1].implode(' ', array_map(
                fn (int $number) => $markdown ? "[#$number]({$this->url->to('forum')->route('discussion', ['id' => $slug, 'near' => $number])})" : "#$number",
                $cited
            ));
        }, $text);

        // A name the model made up must not become a mention of someone who never posted here.
        $text = preg_replace_callback('/(?<![\w@])@([A-Za-z0-9_-]+)/', function (array $match) use ($authors) {
            return in_array(mb_strtolower($match[1]), $authors, true) ? $match[0] : $match[1];
        }, (string) $text);

        return trim((string) $text);
    }

    protected function excerpt(string $content): string
    {
        $content = trim($content);

        if (mb_strlen($content) <= self::MAX_POST_LENGTH) {
            return $content;
        }

        $half = intdiv(self::MAX_POST_LENGTH, 2);

        return mb_substr($content, 0, $half)."\n[…]\n".mb_substr($content, -$half);
    }

    protected function comments(int $discussionId): Builder
    {
        return Post::query()
            ->where('discussion_id', $discussionId)
            ->where('type', CommentPost::$type)
            ->whereNull('hidden_at')
            // flarum/approval marks posts that wait for approval as private.
            ->where('is_private', false);
    }

    protected function lockKey(Discussion $discussion): string
    {
        return "datlechin-ai-summarize.generating.$discussion->id";
    }
}

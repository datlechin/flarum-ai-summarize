<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize;

use Datlechin\Ai\Providers\HttpProviderFactory;
use Flarum\Discussion\Discussion;
use Flarum\Locale\LocaleManager;
use Flarum\Post\CommentPost;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Formatter\Formatter;

class SummarizeService
{
    public function __construct(
        private HttpProviderFactory $providerFactory,
        private SettingsRepositoryInterface $settings,
        private Formatter $formatter,
        private LocaleManager $localeManager
    ) {}

    /**
     * Generate a summary for a discussion
     * 
     * @return array{summary: string}
     */
    public function summarize(Discussion $discussion): array
    {
        $posts = CommentPost::where('discussion_id', $discussion->id)
            ->where('is_private', false)
            ->whereNull('hidden_at')
            ->orderBy('number', 'asc')
            ->get();

        $content = $this->buildContent($discussion, $posts);

        $provider = $this->providerFactory->createLlmProvider();

        $systemPrompt = $this->getSystemPrompt();
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $content],
        ];

        $result = $provider->complete($messages);

        $summaryText = $result['content'] ?? '';
        $formattedSummary = $this->formatToHtml($summaryText);

        return [
            'summary' => $formattedSummary,
        ];
    }

    public function streamSummary(Discussion $discussion): \Generator
    {
        $posts = CommentPost::where('discussion_id', $discussion->id)
            ->where('is_private', false)
            ->whereNull('hidden_at')
            ->orderBy('number', 'asc')
            ->get();

        $content = $this->buildContent($discussion, $posts);

        $provider = $this->providerFactory->createLlmProvider();

        $systemPrompt = $this->getSystemPrompt();
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $content],
        ];

        foreach ($provider->stream($messages) as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Format text to HTML using Flarum's Formatter
     */
    public function formatToHtml(string $text): string
    {
        $parsed = $this->formatter->parse($text);

        $html = $this->formatter->render($parsed);

        return $html;
    }

    private function buildContent(Discussion $discussion, $posts): string
    {
        $totalPosts = $posts->count();

        $content = "Discussion Title: {$discussion->title}\n";
        $content .= "Total Posts to Summarize: {$totalPosts}\n";
        $content .= "========================================\n\n";

        $maxPosts = (int) $this->settings->get('datlechin-ai-summarize.max_posts', 100);
        $postCount = 0;

        foreach ($posts as $post) {
            if ($postCount >= $maxPosts) {
                break;
            }

            $username = $post->user ? $post->user->display_name : 'Unknown';
            $postContent = strip_tags($post->content);
            $postContent = preg_replace('/\s+/', ' ', $postContent);

            $content .= "Post #{$post->number} by {$username}:\n";
            $content .= trim($postContent) . "\n\n";

            $postCount++;
        }

        $content .= "========================================\n";
        $content .= "END OF DISCUSSION - These are ALL the posts. Do not add or invent any additional posts.";

        return $content;
    }

    private function getSystemPrompt(): string
    {
        $customPrompt = $this->settings->get('datlechin-ai-summarize.system_prompt');

        $prompt = ! empty($customPrompt) ? $customPrompt : <<<PROMPT
You are a forum discussion summarizer. Summarize ONLY the exact posts provided between the header and "END OF DISCUSSION" marker.

CRITICAL RULES:
1. The "Total Posts to Summarize" count is absolute—never reference more posts than stated
2. Only summarize content explicitly written in the provided posts
3. Do NOT invent posts, usernames, quotes, arguments, or technical details
4. Do NOT use external knowledge to expand or "improve" the discussion
5. If only 2-3 posts exist, reflect only those posts—no hypothetical additions

BEFORE including any point, verify:
- Is this exact point written in a provided post?
- Can I cite the specific post number and username?
- Does this post number exist within the total count?

OUTPUT REQUIREMENTS:
- 100-200 words
- Neutral, objective language
- Accurately represent the exact number of posts provided
- State if discussion is brief or lacks detail
- No greetings, sign-offs, or meta-commentary

FORMAT: "The discussion contains [X] posts by [Y] user(s). [User A] discusses [exact topic from their post]. [User B] responds with [exact point]. Key points: [only what was actually written]."
PROMPT;

        $locale = $this->localeManager->getLocale();

        if ($locale !== 'en') {
            $prompt .= "\n\nIMPORTANT: You MUST write your entire response in the language corresponding to locale code: {$locale}. Do not respond in English.";
        }

        return $prompt;
    }

    public function canSummarize(Discussion $discussion): bool
    {
        $minPosts = (int) $this->settings->get('datlechin-ai-summarize.min_posts', 3);

        return $discussion->comment_count >= $minPosts;
    }
}

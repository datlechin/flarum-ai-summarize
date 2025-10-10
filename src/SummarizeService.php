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
use Flarum\Post\CommentPost;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Formatter\Formatter;

class SummarizeService
{
    public function __construct(
        private HttpProviderFactory $providerFactory,
        private SettingsRepositoryInterface $settings,
        private Formatter $formatter
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

    /**
     * Stream summary generation
     */
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
        $content = "Discussion Title: {$discussion->title}\n\n";

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

        return $content;
    }

    private function getSystemPrompt(): string
    {
        $customPrompt = $this->settings->get('datlechin-ai-summarize.system_prompt');

        if ($customPrompt) {
            return $customPrompt;
        }

        return <<<PROMPT
You are an AI assistant tasked with summarizing forum discussions. 
Create a concise, informative summary that captures the main points, key arguments, and important conclusions from the discussion.

Your summary should:
- Be clear and easy to understand
- Highlight the main topic and key points
- Mention important insights or conclusions
- Be between 100-200 words
- Use neutral, objective language
- Focus on the content, not the participants

Do not include greetings, sign-offs, or meta-commentary about the summary itself.
PROMPT;
    }

    public function canSummarize(Discussion $discussion): bool
    {
        $minPosts = (int) $this->settings->get('datlechin-ai-summarize.min_posts', 3);

        return $discussion->comment_count >= $minPosts;
    }
}

<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Tests\integration;

use Carbon\Carbon;
use Datlechin\Ai\Extend as AiExtend;
use Flarum\Discussion\Discussion;
use Flarum\Group\Group;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\User\User;
use Psr\Http\Message\ResponseInterface;

trait SummarizesDiscussions
{
    use RetrievesAuthorizedUsers;

    protected const int MODERATOR = 3;

    protected function setUpSummaries(string ...$extensions): void
    {
        FakeDriver::reset();

        $this->extension('flarum-markdown', 'flarum-mentions', ...$extensions);
        $this->extension('datlechin-ai', 'datlechin-ai-summarize');
        $this->extend((new AiExtend\Ai())->driver('fake', FakeDriver::class));

        $this->setting('datlechin-ai.driver', 'fake');
        $this->setting('datlechin-ai.fake.model', 'fake-model');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => self::MODERATOR, 'username' => 'moderator', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'alice', 'email' => 'alice@machine.local', 'is_email_confirmed' => 1],
                ['id' => 5, 'username' => 'bob', 'email' => 'bob@machine.local', 'is_email_confirmed' => 1],
            ],
            'group_user' => [
                ['user_id' => self::MODERATOR, 'group_id' => Group::MODERATOR_ID],
            ],
        ]);
    }

    // Posts alternate between "normal" and "alice"; numbers and ids follow the discussion id, so several discussions can coexist.
    protected function discussionWithPosts(int $id, int $count, array $attributes = []): void
    {
        $lastPostedAt = $attributes['last_posted_at'] ?? Carbon::now()->subHour();

        $this->prepareDatabase([
            Discussion::class => [
                [
                    'id' => $id,
                    'title' => "Discussion $id",
                    'slug' => "discussion-$id",
                    'user_id' => 2,
                    'created_at' => Carbon::parse($lastPostedAt)->subDay(),
                    'comment_count' => $count,
                    'participant_count' => 2,
                    'first_post_id' => $id * 100 + 1,
                    'last_post_id' => $id * 100 + $count,
                    'last_post_number' => $count,
                    'last_posted_at' => $lastPostedAt,
                    'last_posted_user_id' => 2,
                    ...$attributes,
                ],
            ],
            Post::class => array_map(fn (int $number) => [
                'id' => $id * 100 + $number,
                'discussion_id' => $id,
                'number' => $number,
                'user_id' => $number % 2 ? 2 : 4,
                'type' => 'comment',
                'content' => "<t><p>Post $number of discussion $id</p></t>",
                'created_at' => Carbon::parse($lastPostedAt)->subMinutes($count - $number),
            ], range(1, $count)),
        ]);
    }

    protected function existingSummary(int $discussionId, array $postNumbers, array $attributes = []): void
    {
        $this->prepareDatabase([
            'discussion_summaries' => [
                [
                    'id' => $discussionId,
                    'discussion_id' => $discussionId,
                    'user_id' => self::MODERATOR,
                    'content' => '<t><p>An earlier summary.</p></t>',
                    'model' => 'fake-model',
                    'post_ids' => json_encode(array_map(fn (int $number) => $discussionId * 100 + $number, $postNumbers)),
                    'last_post_number' => max($postNumbers),
                    'created_at' => Carbon::now()->subHours(2),
                    ...$attributes,
                ],
            ],
        ]);
    }

    protected function summarize(int $discussionId, ?int $as = self::MODERATOR): ResponseInterface
    {
        $request = $this->request('POST', "/api/discussions/$discussionId/summary", $as ? ['authenticatedAs' => $as] : []);

        return $this->send($as ? $request : $request->withAttribute('bypassCsrfToken', true));
    }

    // The endpoint echoes its NDJSON from a CallbackStream, so the lines arrive as output rather than as the body.
    protected function streamed(ResponseInterface $response): array
    {
        $output = '';

        // A callback, because the endpoint calls ob_flush() after every line.
        ob_start(function (string $chunk) use (&$output) {
            $output .= $chunk;

            return '';
        });
        (string) $response->getBody();
        ob_end_clean();

        return array_map(fn (string $line) => json_decode($line, true), array_values(array_filter(explode("\n", $output))));
    }

    protected function summaryRow(int $discussionId): ?object
    {
        return $this->database()->table('discussion_summaries')->where('discussion_id', $discussionId)->first();
    }
}

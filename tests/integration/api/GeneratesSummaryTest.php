<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Tests\integration\api;

use Carbon\Carbon;
use Datlechin\AiSummarize\Summarizer;
use Datlechin\AiSummarize\Tests\integration\FakeDriver;
use Datlechin\AiSummarize\Tests\integration\SummarizesDiscussions;
use Flarum\Discussion\Discussion;
use Flarum\Testing\integration\TestCase;
use Illuminate\Cache\RateLimiter;
use PHPUnit\Framework\Attributes\Test;

class GeneratesSummaryTest extends TestCase
{
    use SummarizesDiscussions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSummaries();
        $this->discussionWithPosts(1, 4);
    }

    #[Test]
    public function guests_cannot_summarize(): void
    {
        $this->assertSame(403, $this->summarize(1, null)->getStatusCode());
    }

    #[Test]
    public function members_cannot_summarize_by_default(): void
    {
        $this->assertSame(403, $this->summarize(1, 2)->getStatusCode());
    }

    #[Test]
    public function nothing_is_sent_while_no_provider_is_set_up(): void
    {
        $this->setting('datlechin-ai.fake.model', '');

        $this->assertSame(503, $this->summarize(1)->getStatusCode());
        $this->assertSame(0, FakeDriver::$calls);
    }

    #[Test]
    public function a_discussion_shorter_than_the_minimum_is_refused(): void
    {
        $this->setting('datlechin-ai-summarize.min_posts', 5);

        $this->assertSame(422, $this->summarize(1)->getStatusCode());
    }

    #[Test]
    public function an_up_to_date_summary_is_not_written_again(): void
    {
        $this->existingSummary(1, [1, 2, 3, 4]);

        $this->assertSame(422, $this->summarize(1)->getStatusCode());
        $this->assertSame(0, FakeDriver::$calls);
    }

    #[Test]
    public function a_reply_after_the_summary_allows_a_new_one(): void
    {
        $this->existingSummary(1, [1, 2, 3]);

        $response = $this->summarize(1);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('done', last($this->streamed($response))['type']);
        $this->assertSame(4, $this->summaryRow(1)->last_post_number);
    }

    #[Test]
    public function a_summary_being_written_blocks_a_second_one(): void
    {
        $this->app()->getContainer()->make(Summarizer::class)->acquire(Discussion::query()->findOrFail(1));

        $this->assertSame(422, $this->summarize(1)->getStatusCode());
        $this->assertSame(0, FakeDriver::$calls);
    }

    #[Test]
    public function the_seventh_attempt_within_five_minutes_is_throttled(): void
    {
        $limiter = $this->app()->getContainer()->make(RateLimiter::class);

        foreach (range(1, 6) as $attempt) {
            $limiter->hit('datlechin-ai-summarize.'.self::MODERATOR, 300);
        }

        $this->assertSame(429, $this->summarize(1)->getStatusCode());
    }

    #[Test]
    public function the_summary_streams_as_text_lines_then_done_and_is_saved(): void
    {
        $response = $this->summarize(1);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/x-ndjson', $response->getHeaderLine('Content-Type'));
        $this->assertSame([
            ['type' => 'text', 'text' => 'The thread asks about caching [#1]. '],
            ['type' => 'text', 'text' => '@alice suggests Redis [#2].'],
            ['type' => 'done'],
        ], $this->streamed($response));

        $row = $this->summaryRow(1);

        $this->assertSame([101, 102, 103, 104], json_decode($row->post_ids));
        $this->assertSame(4, (int) $row->last_post_number);
        $this->assertSame('fake-model', $row->model);
        $this->assertSame(self::MODERATOR, (int) $row->user_id);
        $this->assertTrue($this->app()->getContainer()->make(Summarizer::class)->acquire(Discussion::query()->findOrFail(1)));
    }

    #[Test]
    public function a_provider_failure_mid_stream_sends_an_error_line_and_saves_nothing(): void
    {
        FakeDriver::$fails = true;

        $response = $this->summarize(1);

        $this->assertSame(200, $response->getStatusCode());

        $lines = $this->streamed($response);

        $this->assertSame('text', $lines[0]['type']);
        $this->assertSame('error', last($lines)['type']);
        $this->assertNull($this->summaryRow(1));
        $this->assertTrue($this->app()->getContainer()->make(Summarizer::class)->acquire(Discussion::query()->findOrFail(1)));
    }

    #[Test]
    public function a_discussion_whose_posts_are_all_hidden_cannot_be_summarized(): void
    {
        $this->database()->table('posts')->where('discussion_id', 1)->update(['hidden_at' => Carbon::now()]);

        $this->assertSame(422, $this->summarize(1)->getStatusCode());
    }
}

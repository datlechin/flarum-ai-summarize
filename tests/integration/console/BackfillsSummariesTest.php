<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Tests\integration\console;

use Carbon\Carbon;
use Datlechin\AiSummarize\Tests\integration\FakeDriver;
use Datlechin\AiSummarize\Tests\integration\SummarizesDiscussions;
use Flarum\Testing\integration\ConsoleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class BackfillsSummariesTest extends ConsoleTestCase
{
    use SummarizesDiscussions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSummaries();

        $this->discussionWithPosts(1, 4, ['last_posted_at' => Carbon::now()->subDay()]);
        $this->discussionWithPosts(2, 5, ['last_posted_at' => Carbon::now()->subMinutes(10)]);
        $this->existingSummary(2, [1, 2, 3]);
        $this->discussionWithPosts(3, 4, ['last_posted_at' => Carbon::now()->subDays(2)]);
        $this->discussionWithPosts(4, 4, ['last_posted_at' => Carbon::now()->subDays(40)]);
        $this->discussionWithPosts(5, 2, ['last_posted_at' => Carbon::now()]);
    }

    #[Test]
    public function nothing_is_summarized_while_the_hourly_budget_is_zero(): void
    {
        $this->assertSame(0, $this->backfill());
        $this->assertSame(0, FakeDriver::$calls);
    }

    #[Test]
    public function each_run_takes_a_sixth_of_the_hourly_budget_starting_with_discussions_that_have_none(): void
    {
        $this->setting('datlechin-ai-summarize.auto_per_hour', 6);

        $this->backfill();
        $this->assertSame([1], $this->summarizedSince());

        $this->backfill();
        $this->assertSame([1, 3], $this->summarizedSince());

        $this->backfill();
        $this->assertSame([1, 2, 3], $this->summarizedSince());
        $this->assertSame(5, (int) $this->summaryRow(2)->last_post_number);
        $this->assertNull($this->summaryRow(2)->user_id);

        $this->backfill();
        $this->assertSame(3, FakeDriver::$calls);
    }

    #[Test]
    public function an_outdated_summary_younger_than_an_hour_is_left_alone(): void
    {
        $this->setting('datlechin-ai-summarize.auto_per_hour', 60);
        $this->app();
        $this->database()->table('discussion_summaries')->where('discussion_id', 2)->update(['created_at' => Carbon::now()->subMinutes(30)]);

        $this->backfill();

        $this->assertSame(2, FakeDriver::$calls);
        $this->assertSame(3, (int) $this->summaryRow(2)->last_post_number);
    }

    #[Test]
    public function a_provider_error_stops_the_run(): void
    {
        $this->setting('datlechin-ai-summarize.auto_per_hour', 60);
        FakeDriver::$fails = true;

        $this->assertSame(1, $this->backfill());
        $this->assertSame(1, FakeDriver::$calls);
        $this->assertSame([], $this->summarizedSince());
    }

    private function backfill(): int
    {
        return $this->console()->run(new ArrayInput(['command' => 'summaries:backfill']), new BufferedOutput());
    }

    private function summarizedSince(): array
    {
        return $this->database()->table('discussion_summaries')
            ->where('created_at', '>', Carbon::now()->subHour())
            ->orderBy('discussion_id')
            ->pluck('discussion_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}

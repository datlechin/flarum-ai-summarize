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
use Datlechin\AiSummarize\Tests\integration\FakeDriver;
use Datlechin\AiSummarize\Tests\integration\SummarizesDiscussions;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ReadsOnlyWhatEveryoneCanSeeTest extends TestCase
{
    use SummarizesDiscussions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSummaries('flarum-likes');
    }

    #[Test]
    public function hidden_posts_and_posts_awaiting_approval_are_left_out(): void
    {
        $this->discussionWithPosts(1, 5);
        $this->app();
        $this->database()->table('posts')->where('id', 102)->update(['hidden_at' => Carbon::now()]);
        $this->database()->table('posts')->where('id', 104)->update(['is_private' => true]);

        $this->streamed($this->summarize(1));

        $this->assertSame([1, 3, 5], $this->numbersInPrompt());
        $this->assertSame([101, 103, 105], json_decode($this->summaryRow(1)->post_ids));
    }

    #[Test]
    public function each_post_is_headed_by_its_number_and_its_author(): void
    {
        $this->discussionWithPosts(1, 3);

        $this->streamed($this->summarize(1));

        $input = FakeDriver::$prompt->messages[0]->text;

        $this->assertStringStartsWith('Title: Discussion 1', $input);
        $this->assertStringContainsString("[#1] @normal:\nPost 1 of discussion 1", $input);
        $this->assertStringContainsString("[#2] @alice:\nPost 2 of discussion 1", $input);
    }

    #[Test]
    public function a_long_discussion_is_read_from_its_start_its_end_and_its_most_liked_posts(): void
    {
        $this->setting('datlechin-ai-summarize.max_posts', 12);
        $this->discussionWithPosts(1, 20);
        $this->prepareDatabase([
            'post_likes' => [
                ['post_id' => 108, 'user_id' => 2],
                ['post_id' => 108, 'user_id' => 4],
                ['post_id' => 112, 'user_id' => 5],
            ],
        ]);

        $this->streamed($this->summarize(1));

        $this->assertSame([1, 2, 3, 4, 5, 8, 12, 16, 17, 18, 19, 20], $this->numbersInPrompt());
    }

    #[Test]
    public function the_instructions_name_the_forum_language_and_add_the_admins_own(): void
    {
        $this->setting('datlechin-ai-summarize.instructions', 'Never mention post counts.');
        $this->discussionWithPosts(1, 3);

        $this->streamed($this->summarize(1));

        $instructions = FakeDriver::$prompt->instructions;

        $this->assertStringContainsString('(en)', $instructions);
        $this->assertStringContainsString('Never mention post counts.', $instructions);
        $this->assertStringContainsString('Use Markdown', $instructions);
    }

    #[Test]
    public function without_extra_instructions_nothing_is_added(): void
    {
        $this->discussionWithPosts(1, 3);

        $this->streamed($this->summarize(1));

        $this->assertStringNotContainsString("The forum's admins", FakeDriver::$prompt->instructions);
    }

    private function numbersInPrompt(): array
    {
        preg_match_all('/^\[#(\d+)\]/m', FakeDriver::$prompt->messages[0]->text, $matches);

        return array_map('intval', $matches[1]);
    }
}

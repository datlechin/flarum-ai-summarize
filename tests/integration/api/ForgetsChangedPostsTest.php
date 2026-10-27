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

use Datlechin\AiSummarize\Tests\integration\SummarizesDiscussions;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ForgetsChangedPostsTest extends TestCase
{
    use SummarizesDiscussions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSummaries();
        $this->discussionWithPosts(1, 4);
        $this->existingSummary(1, [1, 2, 3]);
    }

    #[Test]
    public function hiding_a_summarized_post_removes_the_summary(): void
    {
        $this->assertSame(200, $this->updatePost(102, ['isHidden' => true])->getStatusCode());

        $this->assertNull($this->summaryRow(1));
    }

    #[Test]
    public function editing_a_summarized_post_removes_the_summary(): void
    {
        $this->assertSame(200, $this->updatePost(102, ['content' => 'I take that back.'])->getStatusCode());

        $this->assertNull($this->summaryRow(1));
    }

    #[Test]
    public function deleting_a_summarized_post_removes_the_summary(): void
    {
        $response = $this->send($this->request('DELETE', '/api/posts/102', ['authenticatedAs' => 1]));

        $this->assertSame(204, $response->getStatusCode());
        $this->assertNull($this->summaryRow(1));
    }

    #[Test]
    public function hiding_a_reply_written_after_the_summary_keeps_it(): void
    {
        $this->assertSame(200, $this->updatePost(104, ['isHidden' => true])->getStatusCode());

        $this->assertNotNull($this->summaryRow(1));
    }

    #[Test]
    public function deleting_the_discussion_removes_its_summary(): void
    {
        $response = $this->send($this->request('DELETE', '/api/discussions/1', ['authenticatedAs' => 1]));

        $this->assertSame(204, $response->getStatusCode());
        $this->assertNull($this->summaryRow(1));
    }

    private function updatePost(int $id, array $attributes)
    {
        return $this->send($this->request('PATCH', "/api/posts/$id", [
            'authenticatedAs' => self::MODERATOR,
            'json' => ['data' => ['type' => 'posts', 'id' => (string) $id, 'attributes' => $attributes]],
        ]));
    }
}

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

class RemovesSummaryTest extends TestCase
{
    use SummarizesDiscussions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSummaries();
        $this->discussionWithPosts(1, 4);
        $this->existingSummary(1, [1, 2, 3, 4]);
    }

    #[Test]
    public function a_moderator_can_remove_a_summary(): void
    {
        $response = $this->send($this->request('DELETE', '/api/discussion-summaries/1', ['authenticatedAs' => self::MODERATOR]));

        $this->assertSame(204, $response->getStatusCode());
        $this->assertNull($this->summaryRow(1));
    }

    #[Test]
    public function a_member_cannot_remove_a_summary(): void
    {
        $response = $this->send($this->request('DELETE', '/api/discussion-summaries/1', ['authenticatedAs' => 2]));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertNotNull($this->summaryRow(1));
    }

    #[Test]
    public function the_summary_of_a_discussion_the_actor_cannot_see_is_not_found(): void
    {
        $this->app();
        $this->database()->table('discussions')->where('id', 1)->update(['is_private' => true]);

        $response = $this->send($this->request('DELETE', '/api/discussion-summaries/1', ['authenticatedAs' => 2]));

        $this->assertSame(404, $response->getStatusCode());
    }
}

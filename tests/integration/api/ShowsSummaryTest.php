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

use Datlechin\AiSummarize\Tests\integration\FakeDriver;
use Datlechin\AiSummarize\Tests\integration\SummarizesDiscussions;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ShowsSummaryTest extends TestCase
{
    use SummarizesDiscussions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSummaries();
        $this->discussionWithPosts(1, 4);
    }

    #[Test]
    public function citations_become_links_to_the_cited_posts(): void
    {
        FakeDriver::$chunks = ['Caching came up [#1, #3] and was settled [#2].'];

        $html = $this->generatedHtml();

        $this->assertMatchesRegularExpression('~up <a href="http://localhost/d/1-discussion-1/1"[^>]*>#1</a> <a href="http://localhost/d/1-discussion-1/3"[^>]*>#3</a> and~', $html);
        $this->assertMatchesRegularExpression('~settled <a href="http://localhost/d/1-discussion-1/2"[^>]*>#2</a>\.~', $html);
    }

    #[Test]
    public function a_citation_of_a_post_that_was_not_read_is_dropped_with_its_space(): void
    {
        FakeDriver::$chunks = ['Someone disagreed [#99].'];

        $html = $this->generatedHtml();

        $this->assertStringContainsString('Someone disagreed.', $html);
        $this->assertStringNotContainsString('99', $html);
    }

    #[Test]
    public function only_people_who_posted_are_mentioned(): void
    {
        FakeDriver::$chunks = ['@alice suggests Redis and @bob agrees.'];

        $html = $this->generatedHtml();

        $this->assertMatchesRegularExpression('/<a href="http:\/\/localhost\/u\/alice" class="UserMention"[^>]*>@alice<\/a>/', $html);
        $this->assertStringContainsString('and bob agrees.', $html);
        $this->assertStringNotContainsString('/u/bob', $html);
    }

    #[Test]
    public function the_summary_reports_how_much_it_covers_and_what_came_after(): void
    {
        $this->existingSummary(1, [1, 2, 3]);

        $summary = $this->includedSummary($this->showDiscussion(2));

        $this->assertSame('<p>An earlier summary.</p>', $summary['attributes']['contentHtml']);
        $this->assertSame('fake-model', $summary['attributes']['model']);
        $this->assertSame(3, $summary['attributes']['postCount']);
        $this->assertSame(1, $summary['attributes']['newReplyCount']);
    }

    #[Test]
    public function moderators_can_remove_a_summary_and_members_cannot(): void
    {
        $this->existingSummary(1, [1, 2, 3, 4]);

        $this->assertTrue($this->includedSummary($this->showDiscussion(self::MODERATOR))['attributes']['canDelete']);
        $this->assertFalse($this->includedSummary($this->showDiscussion(2))['attributes']['canDelete']);
    }

    #[Test]
    public function the_discussion_says_whether_the_actor_can_summarize_it_now(): void
    {
        $this->assertTrue($this->showDiscussion(self::MODERATOR)['data']['attributes']['canSummarize']);
        $this->assertFalse($this->showDiscussion(2)['data']['attributes']['canSummarize']);
    }

    #[Test]
    public function an_up_to_date_summary_cannot_be_written_again(): void
    {
        $this->existingSummary(1, [1, 2, 3, 4]);

        $this->assertFalse($this->showDiscussion(self::MODERATOR)['data']['attributes']['canSummarize']);
    }

    #[Test]
    public function the_discussion_list_does_not_compute_whether_each_can_be_summarized(): void
    {
        $response = $this->send($this->request('GET', '/api/discussions', ['authenticatedAs' => self::MODERATOR]));
        $data = json_decode((string) $response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertArrayNotHasKey('canSummarize', $data['data'][0]['attributes']);
    }

    #[Test]
    public function a_group_without_the_view_permission_does_not_get_the_summary(): void
    {
        $this->existingSummary(1, [1, 2, 3, 4]);
        $this->app();
        $this->database()->table('group_permission')->where('permission', 'discussion.viewAiSummary')->delete();

        $document = $this->showDiscussion(2);

        $this->assertArrayNotHasKey('summary', $document['data']['relationships']);
        $this->assertNull($this->includedSummary($document));
    }

    #[Test]
    public function guests_see_the_summary_by_default(): void
    {
        $this->existingSummary(1, [1, 2, 3, 4]);

        $this->assertSame('<p>An earlier summary.</p>', $this->includedSummary($this->showDiscussion(null))['attributes']['contentHtml']);
    }

    private function generatedHtml(): string
    {
        $this->streamed($this->summarize(1));

        return $this->includedSummary($this->showDiscussion(2))['attributes']['contentHtml'];
    }

    private function showDiscussion(?int $as): array
    {
        $response = $this->send($this->request('GET', '/api/discussions/1', $as ? ['authenticatedAs' => $as] : []));

        $this->assertSame(200, $response->getStatusCode());

        return json_decode((string) $response->getBody(), true);
    }

    private function includedSummary(array $document): ?array
    {
        foreach ($document['included'] ?? [] as $resource) {
            if ($resource['type'] === 'discussion-summaries') {
                return $resource;
            }
        }

        return null;
    }
}

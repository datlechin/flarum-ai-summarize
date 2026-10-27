<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Listener;

use Datlechin\AiSummarize\Summary;
use Flarum\Discussion\Event\Deleted as DiscussionDeleted;
use Flarum\Post\Event\Deleted;
use Flarum\Post\Event\Hidden;
use Flarum\Post\Event\Revised;
use Illuminate\Contracts\Events\Dispatcher;

// A summary must not keep repeating what a moderator hid or an author took back.
class DeleteSummaryOfChangedPosts
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen([Hidden::class, Deleted::class, Revised::class], [$this, 'whenPostChanged']);
        $events->listen(DiscussionDeleted::class, [$this, 'whenDiscussionDeleted']);
    }

    public function whenPostChanged(Hidden|Deleted|Revised $event): void
    {
        $summary = Summary::query()->where('discussion_id', $event->post->discussion_id)->first();

        if ($summary?->covers($event->post->id)) {
            $summary->delete();
        }
    }

    // The foreign key cascades on MySQL and PostgreSQL, but SQLite runs with foreign keys off.
    public function whenDiscussionDeleted(DiscussionDeleted $event): void
    {
        Summary::query()->where('discussion_id', $event->discussion->id)->delete();
    }
}

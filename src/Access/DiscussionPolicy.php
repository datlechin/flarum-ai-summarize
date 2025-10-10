<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Access;

use Flarum\Discussion\Discussion;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class DiscussionPolicy extends AbstractPolicy
{
    /**
     * @param User $actor
     * @param string $ability
     * @param Discussion $discussion
     * @return bool|void
     */
    public function can(User $actor, string $ability, Discussion $discussion)
    {
        if ($ability === 'aiSummarize') {
            return $this->aiSummarize($actor, $discussion);
        }
    }

    public function aiSummarize(User $actor, Discussion $discussion): bool
    {
        if (!$actor->can('view', $discussion)) {
            return false;
        }

        return $actor->hasPermission('discussion.aiSummarize');
    }
}

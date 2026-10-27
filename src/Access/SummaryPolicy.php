<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Access;

use Datlechin\AiSummarize\Summary;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class SummaryPolicy extends AbstractPolicy
{
    public function delete(User $actor, Summary $summary): ?string
    {
        // Whoever moderates the posts of a discussion can take down a summary of them.
        return $actor->can('editPosts', $summary->discussion) ? $this->allow() : null;
    }
}

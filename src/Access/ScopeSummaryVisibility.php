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

use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeSummaryVisibility
{
    public function __invoke(User $actor, Builder $query): void
    {
        $query->whereHas('discussion', fn (Builder $discussions) => $discussions->whereVisibleTo($actor));
    }
}

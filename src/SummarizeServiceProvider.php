<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize;

use Flarum\Formatter\Formatter;
use Flarum\Foundation\AbstractServiceProvider;

class SummarizeServiceProvider extends AbstractServiceProvider
{
    public function boot(Formatter $formatter): void
    {
        Summary::setFormatter($formatter);
    }
}

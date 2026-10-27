<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Builder;

// 0.1.x replaced the whole prompt with system_prompt; it is now added to the built-in one. The permission replaces enabled.
return [
    'up' => function (Builder $schema) {
        $settings = $schema->getConnection()->table('settings');

        (clone $settings)->where('key', 'datlechin-ai-summarize.system_prompt')->update(['key' => 'datlechin-ai-summarize.instructions']);
        (clone $settings)->where('key', 'datlechin-ai-summarize.enabled')->delete();
    },

    'down' => function (Builder $schema) {
        $schema->getConnection()->table('settings')
            ->where('key', 'datlechin-ai-summarize.instructions')
            ->update(['key' => 'datlechin-ai-summarize.system_prompt']);
    },
];

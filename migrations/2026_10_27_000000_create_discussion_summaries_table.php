<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        $schema->create('discussion_summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('discussion_id')->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->mediumText('content');
            $table->string('model', 100);
            $table->json('post_ids');
            $table->unsignedInteger('last_post_number');
            $table->dateTime('created_at');

            $table->foreign('discussion_id')->references('id')->on('discussions')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    },

    'down' => function (Builder $schema) {
        $schema->dropIfExists('discussion_summaries');
    },
];

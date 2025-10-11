<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize;

use Flarum\Extend;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Datlechin\AiSummarize\Api\Controller\SummarizeDiscussionController;
use Datlechin\AiSummarize\Access\DiscussionPolicy;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Routes('api'))
        ->get('/ai-summarize/discussions', 'discussions.summarize', SummarizeDiscussionController::class),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->attribute('canSummarize', function (DiscussionSerializer $serializer, Discussion $discussion) {
            return $serializer->getActor()->can('aiSummarize', $discussion);
        }),

    (new Extend\Policy())
        ->modelPolicy(Discussion::class, DiscussionPolicy::class),

    (new Extend\Settings())
        ->default('datlechin-ai-summarize.min_posts', 3)
        ->default('datlechin-ai-summarize.max_posts', 100)
        ->default('datlechin-ai-summarize.enabled', true)
        ->serializeToForum('datlechin-ai-summarize.enabled', 'datlechin-ai-summarize.enabled', 'boolval')
        ->serializeToForum('datlechin-ai-summarize.minPosts', 'datlechin-ai-summarize.min_posts', 'intval'),
];

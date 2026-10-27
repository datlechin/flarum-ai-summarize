<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Datlechin\AiSummarize\Access\ScopeSummaryVisibility;
use Datlechin\AiSummarize\Access\SummaryPolicy;
use Datlechin\AiSummarize\Api\Controller\GenerateSummaryController;
use Datlechin\AiSummarize\Api\DiscussionResourceFields;
use Datlechin\AiSummarize\Api\Resource\SummaryResource;
use Datlechin\AiSummarize\Console\BackfillSummariesCommand;
use Datlechin\AiSummarize\Listener\DeleteSummaryOfChangedPosts;
use Datlechin\AiSummarize\SummarizeServiceProvider;
use Datlechin\AiSummarize\Summary;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\DiscussionResource;
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Illuminate\Console\Scheduling\Event;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ServiceProvider())
        ->register(SummarizeServiceProvider::class),

    (new Extend\Model(Discussion::class))
        ->hasOne('summary', Summary::class),

    new Extend\ApiResource(SummaryResource::class),

    (new Extend\ApiResource(DiscussionResource::class))
        ->fields(DiscussionResourceFields::class)
        ->endpoint(Endpoint\Show::class, fn (Endpoint\Show $endpoint) => $endpoint->addDefaultInclude(['summary'])),

    (new Extend\Routes('api'))
        ->post('/discussions/{id}/summary', 'discussions.summary.create', GenerateSummaryController::class),

    (new Extend\Policy())
        ->modelPolicy(Summary::class, SummaryPolicy::class),

    (new Extend\ModelVisibility(Summary::class))
        ->scope(ScopeSummaryVisibility::class),

    (new Extend\Event())
        ->subscribe(DeleteSummaryOfChangedPosts::class),

    (new Extend\Console())
        ->command(BackfillSummariesCommand::class)
        ->schedule(BackfillSummariesCommand::class, function (Event $event) {
            $event->everyTenMinutes()->withoutOverlapping();
        }),

    (new Extend\Settings())
        ->default('datlechin-ai-summarize.min_posts', 3)
        ->default('datlechin-ai-summarize.max_posts', 100)
        ->default('datlechin-ai-summarize.auto_per_hour', 0),
];

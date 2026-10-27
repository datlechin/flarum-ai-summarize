<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Api\Resource;

use Datlechin\AiSummarize\Summarizer;
use Datlechin\AiSummarize\Summary;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context as OriginalContext;

class SummaryResource extends AbstractDatabaseResource
{
    public function __construct(
        protected Summarizer $summarizer
    ) {
    }

    public function type(): string
    {
        return 'discussion-summaries';
    }

    public function model(): string
    {
        return Summary::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        $query->whereVisibleTo($context->getActor());
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Delete::make()
                ->authenticated()
                ->can('delete'),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('contentHtml')
                ->get(fn (Summary $summary, Context $context) => $summary->formatContent($context->request)),
            Schema\Str::make('model'),
            Schema\DateTime::make('createdAt'),
            Schema\Integer::make('postCount')
                ->get(fn (Summary $summary) => count($summary->post_ids)),
            Schema\Integer::make('newReplyCount')
                ->get(fn (Summary $summary) => $this->summarizer->newReplyCount($summary)),
            Schema\Boolean::make('canDelete')
                ->get(fn (Summary $summary, Context $context) => $context->getActor()->can('delete', $summary)),

            Schema\Relationship\ToOne::make('discussion')
                ->inverse('summary')
                ->type('discussions'),
        ];
    }
}

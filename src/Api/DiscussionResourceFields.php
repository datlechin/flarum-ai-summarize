<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Api;

use Datlechin\AiSummarize\Summarizer;
use Flarum\Api\Context;
use Flarum\Api\Resource\DiscussionResource;
use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;

class DiscussionResourceFields
{
    public function __construct(
        protected Summarizer $summarizer
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Relationship\ToOne::make('summary')
                ->type('discussion-summaries')
                ->includable()
                ->visible(fn (Discussion $discussion, Context $context) => $context->getActor()->can('viewAiSummary', $discussion)),

            // Only shown on a single discussion, since it needs the summary and a count of the replies after it.
            Schema\Boolean::make('canSummarize')
                ->visible(fn (Discussion $discussion, Context $context) => $context->showing(DiscussionResource::class))
                ->get(fn (Discussion $discussion, Context $context) => $context->getActor()->can('aiSummarize', $discussion)
                    && $this->summarizer->canGenerate($discussion)),
        ];
    }
}

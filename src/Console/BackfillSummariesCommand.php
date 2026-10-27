<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Console;

use Carbon\Carbon;
use Datlechin\Ai\Ai;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\AiSummarize\Summarizer;
use Flarum\Console\AbstractCommand;
use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class BackfillSummariesCommand extends AbstractCommand
{
    // The scheduler runs this every ten minutes, so each run takes a sixth of the hourly budget.
    public const int RUNS_PER_HOUR = 6;

    public const int MAX_AGE_DAYS = 30;

    public function __construct(
        protected Summarizer $summarizer,
        protected Ai $ai,
        protected SettingsRepositoryInterface $settings,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('summaries:backfill')
            ->setDescription('Summarize recently active discussions that have no summary or an outdated one.');
    }

    protected function fire(): int
    {
        $perHour = (int) $this->settings->get('datlechin-ai-summarize.auto_per_hour');

        if ($perHour <= 0 || ! $this->ai->isConfigured()) {
            return 0;
        }

        $written = 0;

        foreach ($this->candidates((int) ceil($perHour / self::RUNS_PER_HOUR)) as $discussion) {
            if (! $this->summarizer->canGenerate($discussion) || ! $this->summarizer->acquire($discussion)) {
                continue;
            }

            try {
                $this->summarizer->generate($discussion);
                $written++;
            } catch (ProviderException $e) {
                // A key or quota problem would fail every discussion the same way, so stop until the next run.
                $this->error($e->getMessage());

                return 1;
            } finally {
                $this->summarizer->release($discussion);
            }
        }

        $this->info("Wrote $written summaries.");

        return 0;
    }

    protected function candidates(int $limit): iterable
    {
        $missing = $this->recent()->whereDoesntHave('summary')->limit($limit)->get();

        if ($missing->count() >= $limit) {
            return $missing;
        }

        $outdated = $this->recent()
            ->whereHas('summary', fn (Builder $query) => $query
                ->whereColumn('discussions.last_post_number', '>', 'discussion_summaries.last_post_number')
                // A busy discussion gets a fresh summary at most once an hour.
                ->where('discussion_summaries.created_at', '<', Carbon::now()->subHour()))
            ->limit($limit - $missing->count())
            ->get();

        return $missing->concat($outdated);
    }

    protected function recent(): Builder
    {
        return Discussion::query()
            ->whereNull('hidden_at')
            ->where('is_private', false)
            ->where('comment_count', '>=', $this->summarizer->minPosts())
            ->where('last_posted_at', '>=', Carbon::now()->subDays(self::MAX_AGE_DAYS))
            ->orderByDesc('last_posted_at')
            ->with('summary');
    }
}

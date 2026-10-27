<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Api\Controller;

use Datlechin\Ai\Ai;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\AiSummarize\Summarizer;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\Locale\TranslatorInterface;
use Flarum\Post\Exception\FloodingException;
use Flarum\User\User;
use Generator;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Arr;
use Laminas\Diactoros\CallbackStream;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class GenerateSummaryController implements RequestHandlerInterface
{
    // Discourse's limit for the same feature.
    public const int ATTEMPTS = 6;

    public const int DECAY_SECONDS = 300;

    public function __construct(
        protected DiscussionRepository $discussions,
        protected Summarizer $summarizer,
        protected Ai $ai,
        protected RateLimiter $limiter,
        protected TranslatorInterface $translator,
        protected LoggerInterface $logger,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $discussion = $this->discussions->findOrFail(Arr::get($request->getQueryParams(), 'id'), $actor);

        $actor->assertCan('aiSummarize', $discussion);
        $this->summarizer->assertCanGenerate($discussion);

        $key = "datlechin-ai-summarize.$actor->id";

        if ($this->limiter->tooManyAttempts($key, self::ATTEMPTS)) {
            throw new FloodingException();
        }

        if (! $this->summarizer->acquire($discussion)) {
            throw new ValidationException(['summary' => $this->translator->trans('datlechin-ai-summarize.api.busy_message')]);
        }

        $this->limiter->hit($key, self::DECAY_SECONDS);

        try {
            $posts = $this->summarizer->posts($discussion);
            $stream = $this->ai->stream($this->summarizer->prompt($discussion, $posts));
        } catch (Throwable $e) {
            $this->summarizer->release($discussion);

            throw $e;
        }

        return (new Response())
            ->withHeader('Content-Type', 'application/x-ndjson')
            ->withHeader('Cache-Control', 'no-cache, no-transform')
            ->withHeader('X-Accel-Buffering', 'no')
            ->withBody(new CallbackStream($this->write($discussion, $posts, $stream, $actor)));
    }

    protected function write(Discussion $discussion, array $posts, Generator $stream, User $actor): callable
    {
        return function () use ($discussion, $posts, $stream, $actor): string {
            // The tokens are paid for once the model starts, so a reader who leaves still gets the summary saved.
            ignore_user_abort(true);

            $line = function (array $data): void {
                echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";

                // PHP's default output_buffering of 4096 would otherwise hold the lines back in 4 KB batches.
                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();
            };

            try {
                foreach ($stream as $text) {
                    $line(['type' => 'text', 'text' => $text]);
                }

                $this->summarizer->save($discussion, $posts, $stream->getReturn(), $actor);

                $line(['type' => 'done']);
            } catch (Throwable $e) {
                // The headers are out, so the failure has to travel in the body. The AI layer has logged provider errors already.
                if (! $e instanceof ProviderException) {
                    $this->logger->error($e);
                }

                $line(['type' => 'error', 'message' => $this->translator->trans('datlechin-ai-summarize.api.failed_message')]);
            } finally {
                $this->summarizer->release($discussion);
            }

            return '';
        };
    }
}

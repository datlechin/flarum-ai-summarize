<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Api\Controller;

use Datlechin\AiSummarize\SummarizeService;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Psr7\Utils;

class SummarizeDiscussionController implements RequestHandlerInterface
{
    public function __construct(
        private DiscussionRepository $discussions,
        private SummarizeService $summarizeService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $discussionId = Arr::get($request->getQueryParams(), 'id');
        $stream = Arr::get($request->getQueryParams(), 'stream', false);

        $discussion = $this->discussions->findOrFail($discussionId, $actor);

        if (!$actor->hasPermission('discussion.aiSummarize')) {
            $actor->assertCan('aiSummarize', $discussion);
        }

        if (!$this->summarizeService->canSummarize($discussion)) {
            return new JsonResponse([
                'errors' => [
                    [
                        'status' => '400',
                        'code' => 'not_enough_posts',
                        'title' => 'Bad Request',
                        'detail' => 'This discussion does not have enough posts to summarize.'
                    ]
                ]
            ], 400);
        }

        try {
            if ($stream) {
                return $this->handleStream($discussion);
            }

            $summary = $this->summarizeService->summarize($discussion);

            return new JsonResponse([
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => [
                    [
                        'status' => '500',
                        'code' => 'summarization_failed',
                        'title' => 'Internal Server Error',
                        'detail' => 'Failed to generate summary: ' . $e->getMessage()
                    ]
                ]
            ], 500);
        }
    }

    private function handleStream($discussion): ResponseInterface
    {
        $body = Utils::streamFor(function () use ($discussion) {
            echo "data: " . json_encode([
                'type' => 'start',
                'data' => ['message' => 'Generating summary...']
            ]) . "\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            $fullContent = '';
            foreach ($this->summarizeService->streamSummary($discussion) as $chunk) {
                $fullContent .= $chunk;
                echo "data: " . json_encode([
                    'type' => 'content',
                    'data' => $chunk
                ]) . "\n\n";
                
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            // Send formatted HTML version
            $html = $this->summarizeService->formatToHtml($fullContent);
            echo "data: " . json_encode([
                'type' => 'complete',
                'data' => [
                    'html' => $html
                ]
            ]) . "\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            echo "data: [DONE]\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        });

        return new Response($body, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}

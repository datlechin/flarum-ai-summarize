<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize\Tests\integration;

use Datlechin\Ai\Driver\DriverInterface;
use Datlechin\Ai\Exception\ProviderException;
use Datlechin\Ai\Prompt;
use Datlechin\Ai\Response;
use Datlechin\Ai\Usage;
use Flarum\Settings\SettingsRepositoryInterface;
use Generator;

class FakeDriver implements DriverInterface
{
    public static ?Prompt $prompt = null;

    public static array $chunks = ['The thread asks about caching [#1]. ', '@alice suggests Redis [#2].'];

    public static bool $fails = false;

    public static int $calls = 0;

    public static function reset(): void
    {
        self::$prompt = null;
        self::$chunks = ['The thread asks about caching [#1]. ', '@alice suggests Redis [#2].'];
        self::$fails = false;
        self::$calls = 0;
    }

    public function label(): string
    {
        return 'Fake';
    }

    public function fields(): array
    {
        return [];
    }

    public function models(SettingsRepositoryInterface $settings): array
    {
        return ['fake-model'];
    }

    public function generate(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Response
    {
        self::$prompt = $prompt;
        self::$calls++;

        if (self::$fails) {
            throw new ProviderException('Quota exceeded.', 429);
        }

        return new Response(implode('', self::$chunks), $model, new Usage(10, 5));
    }

    public function stream(SettingsRepositoryInterface $settings, string $model, Prompt $prompt): Generator
    {
        self::$prompt = $prompt;
        self::$calls++;

        foreach (self::$chunks as $index => $chunk) {
            if (self::$fails && $index > 0) {
                throw new ProviderException('The connection dropped.');
            }

            yield $chunk;
        }

        return new Response(implode('', self::$chunks), $model, new Usage(10, 5));
    }
}

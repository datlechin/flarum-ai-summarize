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

use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RenamesOldSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('datlechin-ai', 'datlechin-ai-summarize');
    }

    #[Test]
    public function the_old_custom_prompt_becomes_extra_instructions_and_the_on_switch_goes(): void
    {
        $this->app();
        $this->database()->table('settings')->insert([
            ['key' => 'datlechin-ai-summarize.system_prompt', 'value' => 'Answer in Spanish.'],
            ['key' => 'datlechin-ai-summarize.enabled', 'value' => '1'],
            ['key' => 'datlechin-ai-summarize.min_posts', 'value' => '5'],
        ]);

        $migration = require __DIR__.'/../../migrations/2026_10_27_000002_rename_summary_settings.php';
        $migration['up']($this->database()->getSchemaBuilder());

        $settings = $this->database()->table('settings')->where('key', 'like', 'datlechin-ai-summarize.%')->orderBy('key')->pluck('value', 'key')->all();

        $this->assertSame([
            'datlechin-ai-summarize.instructions' => 'Answer in Spanish.',
            'datlechin-ai-summarize.min_posts' => '5',
        ], $settings);
    }
}

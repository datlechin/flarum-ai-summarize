<?php

/*
 * This file is part of datlechin/flarum-ai-summarize.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Datlechin\AiSummarize;

use Flarum\Database\AbstractModel;
use Flarum\Database\ScopeVisibilityTrait;
use Flarum\Discussion\Discussion;
use Flarum\Formatter\Formattable;
use Flarum\Formatter\HasFormattedContent;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $discussion_id
 * @property int|null $user_id
 * @property string $content
 * @property string $model
 * @property int[] $post_ids
 * @property int $last_post_number
 * @property \Carbon\Carbon $created_at
 * @property-read Discussion $discussion
 * @property-read User|null $user
 */
class Summary extends AbstractModel implements Formattable
{
    use HasFormattedContent;
    use ScopeVisibilityTrait;

    protected $table = 'discussion_summaries';

    public const UPDATED_AT = null;

    protected $casts = [
        'post_ids' => 'array',
        'created_at' => 'datetime',
    ];

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(Discussion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function covers(int $postId): bool
    {
        return in_array($postId, $this->post_ids, true);
    }
}

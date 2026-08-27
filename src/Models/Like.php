<?php

// src/Models/Like.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelLikes\Casts\LikeCast;
use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;
use AndyDefer\Repository\Proxies\AttributeProxy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Like model representing user reactions.
 *
 * @property int $id
 * @property string $liker_type
 * @property int $liker_id
 * @property string $likeable_type
 * @property int $likeable_id
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model|null $liker
 * @property-read Model|null $likeable
 * @property-read LikeTypeInterface|null $type
 * @property-read StrictDataObject|null $metadata_object
 */
final class Like extends Model
{
    use SoftDeletes;

    protected $table = 'likes';

    protected $fillable = [
        'liker_type',
        'liker_id',
        'likeable_type',
        'likeable_id',
        'type',
        'metadata',
    ];

    protected $casts = [
        'type' => LikeCast::class,
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============ Relations ============

    public function liker()
    {
        return $this->morphTo();
    }

    public function likeable()
    {
        return $this->morphTo();
    }

    // ============ Attributes ============

    protected function metadata(): Attribute
    {
        return AttributeProxy::nullable(
            StrictDataObject::class,
            column: 'metadata',
        );
    }
}

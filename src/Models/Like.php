<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'type' => LikeType::class,
        'metadata' => 'array',
    ];

    public function liker()
    {
        return $this->morphTo();
    }

    public function likeable()
    {
        return $this->morphTo();
    }

    public function getCreatedAt(): ?DateTimeVO
    {
        $value = $this->created_at;

        return $value ? new DateTimeVO($value) : null;
    }

    public function getUpdatedAt(): ?DateTimeVO
    {
        $value = $this->updated_at;

        return $value ? new DateTimeVO($value) : null;
    }

    public function getDeletedAt(): ?DateTimeVO
    {
        $value = $this->deleted_at;

        return $value ? new DateTimeVO($value) : null;
    }

    public function getMetadata(): ?StrictDataObject
    {
        $value = $this->metadata;

        if ($value === null) {
            return null;
        }

        return is_array($value) ? new StrictDataObject($value) : null;
    }

    public function getType(): LikeType
    {
        return $this->type;
    }
}

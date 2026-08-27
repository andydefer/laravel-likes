<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

/**
 * Data DTO for Like responses.
 *
 * Used to expose like data in API responses without exposing internal model details.
 * Fields are automatically normalized to camelCase for API consistency.
 *
 * @example
 * $likeData = LikeData::from([
 *     'id' => 1,
 *     'liker_type' => 'App\\Models\\User',
 *     'liker_id' => 42,
 *     'likeable_type' => 'App\\Models\\Post',
 *     'likeable_id' => 15,
 *     'type' => 'love',
 *     'metadata' => ['ip' => '192.168.1.1'],
 *     'created_at' => '2024-01-15T10:00:00Z',
 * ]);
 */
final class LikeData extends AbstractData
{
    public function __construct(
        public readonly int $id,
        public readonly string $likerType,
        public readonly int $likerId,
        public readonly string $likeableType,
        public readonly int $likeableId,
        public readonly LikeType $type,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeVO $createdAt = null,
        public readonly ?DateTimeVO $updatedAt = null,
        public readonly ?DateTimeVO $deletedAt = null,
    ) {}
}

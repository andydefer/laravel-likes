<?php

// vendor/andydefer/laravel-likes/src/Datas/LikeData.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

/**
 * Data DTO for Like responses.
 *
 * Used to expose like data in API responses without exposing internal model details.
 * Fields are automatically normalized to camelCase for API consistency.
 *
 * @example
 * $likeData = LikeData::from([
 *     'id' => '0190b0e2-3f4a-7c8b-9d1e-2f3a4b5c6d7e',
 *     'liker_type' => 'App\\Models\\User',
 *     'liker_id' => '0190b0e2-...-...',
 *     'likeable_type' => 'App\\Models\\Post',
 *     'likeable_id' => '0190b0e2-...-...',
 *     'type' => 'favorite',
 *     'metadata' => ['ip' => '192.168.1.1'],
 *     'created_at' => '2024-01-15T10:00:00Z',
 * ]);
 */
final class LikeData extends AbstractData
{
    public function __construct(
        public readonly string $id,
        public readonly string $likerType,
        public readonly string $likerId,
        public readonly string $likeableType,
        public readonly string $likeableId,
        public readonly string $type,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeVO $createdAt = null,
        public readonly ?DateTimeVO $updatedAt = null,
        public readonly ?DateTimeVO $deletedAt = null,
    ) {}
}

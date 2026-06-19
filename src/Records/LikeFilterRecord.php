<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

final class LikeFilterRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?string $liker_type = null,
        public readonly ?int $liker_id = null,
        public readonly ?string $likeable_type = null,
        public readonly ?int $likeable_id = null,
        public readonly ?LikeType $type = null,
        public readonly ?DateTimeVO $updated_at = null,
    ) {}
}

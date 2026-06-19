<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

final class LikeRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $liker_type = null,
        public readonly ?int $liker_id = null,
        public readonly ?string $likeable_type = null,
        public readonly ?int $likeable_id = null,
        public readonly ?LikeType $type = null,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeVO $created_at = null,
        public readonly ?DateTimeVO $updated_at = null,
        public readonly ?DateTimeVO $deleted_at = null,
    ) {}
}

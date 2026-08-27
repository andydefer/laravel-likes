<?php

// src/Contracts/Configs/LikeConfigInterface.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Contracts\Configs;

use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;

interface LikeConfigInterface
{
    /**
     * Get the FQCN of the like type enum.
     *
     * @return class-string<LikeTypeInterface>
     */
    public function getLikeTypeEnumClass(): string;

    /**
     * Get the list of available like types.
     *
     * @return array<LikeTypeInterface>
     */
    public function getAvailableLikeTypes(): array;

    /**
     * Check if a type is valid.
     */
    public function isValidType(string $type): bool;

    /**
     * Get a like type by its value.
     */
    public function getLikeType(string $value): ?LikeTypeInterface;
}

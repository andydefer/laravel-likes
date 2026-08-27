<?php

// src/Contracts/LikeTypeInterface.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Contracts;

/**
 * Interface for like types.
 *
 * Any enum implementing this interface can be used as a like type.
 * This allows developers to define their own like types.
 */
interface LikeTypeInterface
{
    /**
     * Get the emoji representation of the like type.
     */
    public function getEmoji(): string;

    /**
     * Get the human-readable label of the like type.
     */
    public function getLabel(): string;

    /**
     * Get the value of the like type (string or int).
     */
    public function getValue(): string|int;

    /**
     * Get all available cases.
     *
     * @return array<static>
     */
    public static function cases(): array;

    /**
     * Try to get a case by its value.
     */
    public static function tryFrom(string|int $value): ?static;
}

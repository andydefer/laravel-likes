<?php

// src/Contracts/LikeTypeInterface.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Contracts;

use AndyDefer\Repository\Contracts\EnumerableInterface;

/**
 * Interface for like types.
 *
 * Any enum implementing this interface can be used as a like type.
 * This allows developers to define their own like types.
 *
 * @extends EnumerableInterface
 */
interface LikeTypeInterface extends EnumerableInterface
{
    /**
     * Get the emoji representation of the like type.
     */
    public function getEmoji(): string;

    /**
     * Get the human-readable label of the like type.
     */
    public function getLabel(): string;
}

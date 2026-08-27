<?php

// src/Contracts/Services/LikeServiceInterface.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Contracts\Services;

use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Contracts\EnumerableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Interface for the Like service.
 *
 * Defines the contract for managing likes and reactions on models.
 */
interface LikeServiceInterface
{
    /**
     * Toggle a like (add, change, or remove).
     *
     * @param  Model  $liker  The user liking
     * @param  Model  $likeable  The object being liked
     * @param  EnumerableInterface  $type  The type of like
     * @return bool True if a like exists, false if removed
     */
    public function toggle(Model $liker, Model $likeable, EnumerableInterface $type): bool;

    /**
     * Add a like.
     *
     * @param  Model  $liker  The user liking
     * @param  Model  $likeable  The object being liked
     *
     * @throws RuntimeException If already liked
     */
    public function like(Model $liker, Model $likeable): void;

    /**
     * Remove a like.
     *
     * @param  Model  $liker  The user unliking
     * @param  Model  $likeable  The object being unliked
     *
     * @throws RuntimeException If not liked
     */
    public function unlike(Model $liker, Model $likeable): void;

    /**
     * Check if a user has liked an object.
     *
     * @param  Model  $liker  The user
     * @param  Model  $likeable  The object
     * @return bool True if liked
     */
    public function hasLiked(Model $liker, Model $likeable): bool;

    /**
     * Count all likes for an object.
     *
     * @param  Model  $likeable  The object
     * @return int Total number of likes
     */
    public function countLikes(Model $likeable): int;

    /**
     * Count likes by type for an object.
     *
     * @param  Model  $likeable  The object
     * @param  EnumerableInterface  $type  The like type
     * @return int Number of likes of this type
     */
    public function countLikesByType(Model $likeable, EnumerableInterface $type): int;

    /**
     * Get all likers of an object.
     *
     * @param  Model  $likeable  The object
     * @return Collection Collection of liker models
     */
    public function getLikers(Model $likeable): Collection;

    /**
     * Get likers by type for an object.
     *
     * @param  Model  $likeable  The object
     * @param  EnumerableInterface  $type  The like type
     * @return Collection Collection of liker models
     */
    public function getLikersByType(Model $likeable, EnumerableInterface $type): Collection;

    /**
     * Get all likes from a user.
     *
     * @param  Model  $liker  The user
     * @return Collection Collection of likes
     */
    public function getLikerLikes(Model $liker): Collection;

    /**
     * Get likes from a user by type.
     *
     * @param  Model  $liker  The user
     * @param  EnumerableInterface  $type  The like type
     * @return Collection Collection of likes
     */
    public function getLikerLikesByType(Model $liker, EnumerableInterface $type): Collection;

    /**
     * Get all likes updated after a date.
     *
     * @param  DateTimeVO  $date  The date
     * @return Collection Collection of likes
     */
    public function getLikesUpdatedAfter(DateTimeVO $date): Collection;

    /**
     * Get likes from a user updated after a date.
     *
     * @param  Model  $liker  The user
     * @param  DateTimeVO  $date  The date
     * @return Collection Collection of likes
     */
    public function getLikerLikesUpdatedAfter(Model $liker, DateTimeVO $date): Collection;

    /**
     * Get likes for an object updated after a date.
     *
     * @param  Model  $likeable  The object
     * @param  DateTimeVO  $date  The date
     * @return Collection Collection of likes
     */
    public function getLikesForLikeableUpdatedAfter(Model $likeable, DateTimeVO $date): Collection;
}

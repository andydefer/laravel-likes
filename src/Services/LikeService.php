<?php

// src/Services/LikeService.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Services;

use AndyDefer\LaravelLikes\Contracts\Repositories\LikeRepositoryInterface;
use AndyDefer\LaravelLikes\Contracts\Services\LikeServiceInterface;
use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\LaravelLikes\Records\LikeFilterRecord;
use AndyDefer\LaravelLikes\Records\LikeRecord;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Contracts\EnumerableInterface;
use AndyDefer\Repository\Records\FindByRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Service for managing likes and reactions.
 *
 * @implements LikeServiceInterface
 */
final class LikeService implements LikeServiceInterface
{
    public function __construct(
        private readonly LikeRepositoryInterface $likeRepository,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toggle(Model $liker, Model $likeable, EnumerableInterface $type): bool
    {
        $existing = $this->findExisting($liker, $likeable);

        if ($existing) {
            if ($existing->type->getValue() === $type->getValue()) {
                $this->likeRepository->delete($existing->id);

                return false;
            }

            $updateRecord = LikeRecord::from(['type' => $type]);
            $this->likeRepository->update($existing->id, $updateRecord);

            return true;
        }

        $record = LikeRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
            'type' => $type,
        ]);

        $this->likeRepository->create($record);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function like(Model $liker, Model $likeable): void
    {
        $existing = $this->findExisting($liker, $likeable);

        if ($existing) {
            throw new RuntimeException(sprintf(
                'User %s has already liked %s %s',
                $liker->getKey(),
                $likeable->getMorphClass(),
                $likeable->getKey()
            ));
        }

        $record = LikeRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
            'type' => LikeType::LIKE,
        ]);

        $this->likeRepository->create($record);
    }

    /**
     * {@inheritDoc}
     */
    public function unlike(Model $liker, Model $likeable): void
    {
        $existing = $this->findExisting($liker, $likeable);

        if (! $existing) {
            throw new RuntimeException(sprintf(
                'User %s has not liked %s %s',
                $liker->getKey(),
                $likeable->getMorphClass(),
                $likeable->getKey()
            ));
        }

        $this->likeRepository->delete($existing->id);
    }

    /**
     * {@inheritDoc}
     */
    public function hasLiked(Model $liker, Model $likeable): bool
    {
        $filter = LikeFilterRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
        ]);

        return $this->likeRepository->exists($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function countLikes(Model $likeable): int
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
        ]);

        return $this->likeRepository->count($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function countLikesByType(Model $likeable, EnumerableInterface $type): int
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
            'type' => $type,
        ]);

        return $this->likeRepository->count($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function getLikers(Model $likeable): Collection
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    /**
     * {@inheritDoc}
     */
    public function getLikersByType(Model $likeable, EnumerableInterface $type): Collection
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
            'type' => $type,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    /**
     * {@inheritDoc}
     */
    public function getLikerLikes(Model $liker): Collection
    {
        $filter = LikeFilterRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    /**
     * {@inheritDoc}
     */
    public function getLikerLikesByType(Model $liker, EnumerableInterface $type): Collection
    {
        $filter = LikeFilterRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
            'type' => $type,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    /**
     * {@inheritDoc}
     */
    public function getLikesUpdatedAfter(DateTimeVO $date): Collection
    {
        $filter = LikeFilterRecord::from([
            'updated_at' => $date,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    /**
     * {@inheritDoc}
     */
    public function getLikerLikesUpdatedAfter(Model $liker, DateTimeVO $date): Collection
    {
        $filter = LikeFilterRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
            'updated_at' => $date,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    /**
     * {@inheritDoc}
     */
    public function getLikesForLikeableUpdatedAfter(Model $likeable, DateTimeVO $date): Collection
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
            'updated_at' => $date,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    /**
     * Find existing like for a user and object.
     *
     * @param  Model  $liker  The user
     * @param  Model  $likeable  The object
     * @return Model|null The like model or null
     */
    private function findExisting(Model $liker, Model $likeable): ?Model
    {
        $filter = LikeFilterRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
        ]);

        $findByRecord = new FindByRecord(
            filters: $filter,
            limit: 1,
        );

        $collection = $this->likeRepository->findBy($findByRecord);

        return $collection->first();
    }
}

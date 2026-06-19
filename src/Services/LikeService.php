<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Services;

use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\LaravelLikes\Records\LikeFilterRecord;
use AndyDefer\LaravelLikes\Records\LikeRecord;
use AndyDefer\LaravelLikes\Repositories\LikeRepository;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Records\FindByRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

final class LikeService
{
    public function __construct(
        private readonly LikeRepository $likeRepository,
    ) {}

    public function toggle(Model $liker, Model $likeable, LikeType $type = LikeType::LIKE): bool
    {
        $existing = $this->findExisting($liker, $likeable);

        if ($existing) {
            if ($existing->type === $type) {
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

    public function countLikes(Model $likeable): int
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
        ]);

        return $this->likeRepository->count($filter);
    }

    public function countLikesByType(Model $likeable, LikeType $type): int
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
            'type' => $type,
        ]);

        return $this->likeRepository->count($filter);
    }

    public function getLikers(Model $likeable): Collection
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    public function getLikersByType(Model $likeable, LikeType $type): Collection
    {
        $filter = LikeFilterRecord::from([
            'likeable_type' => $likeable->getMorphClass(),
            'likeable_id' => $likeable->getKey(),
            'type' => $type,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    public function getLikerLikes(Model $liker): Collection
    {
        $filter = LikeFilterRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    public function getLikerLikesByType(Model $liker, LikeType $type): Collection
    {
        $filter = LikeFilterRecord::from([
            'liker_type' => $liker->getMorphClass(),
            'liker_id' => $liker->getKey(),
            'type' => $type,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

    public function getLikesUpdatedAfter(DateTimeVO $date): Collection
    {
        $filter = LikeFilterRecord::from([
            'updated_at' => $date,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->likeRepository->findBy($findByRecord);
    }

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
}

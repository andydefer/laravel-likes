<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Repositories;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelLikes\Models\Like;
use AndyDefer\LaravelLikes\Records\LikeFilterRecord;
use AndyDefer\LaravelLikes\Records\LikeRecord;
use AndyDefer\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class LikeRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Like::class,
            recordClass: LikeRecord::class,
        );
    }

    protected function applyFilters(Builder $query, AbstractRecord $filters): void
    {
        if (! $filters instanceof LikeFilterRecord) {
            return;
        }

        if ($filters->liker_type !== null) {
            $query->where('liker_type', $filters->liker_type);
        }

        if ($filters->liker_id !== null) {
            $query->where('liker_id', $filters->liker_id);
        }

        if ($filters->likeable_type !== null) {
            $query->where('likeable_type', $filters->likeable_type);
        }

        if ($filters->likeable_id !== null) {
            $query->where('likeable_id', $filters->likeable_id);
        }

        if ($filters->type !== null) {
            $query->where('type', $filters->type->value);
        }

        if ($filters->updated_at !== null) {
            $query->where('updated_at', '>=', $filters->updated_at->toDateTimeString());
        }
    }
}

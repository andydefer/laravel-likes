<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Services;

use AndyDefer\LaravelLikes\Enums\CommentStatus;
use AndyDefer\LaravelLikes\Records\CommentFilterRecord;
use AndyDefer\LaravelLikes\Records\CommentRecord;
use AndyDefer\LaravelLikes\Repositories\CommentRepository;
use AndyDefer\Repository\Records\FindByRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

final class CommentService
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
    ) {}

    public function add(Model $commenter, Model $commentable, string $content, ?int $parentId = null): Model
    {
        $record = CommentRecord::from([
            'commenter_type' => $commenter->getMorphClass(),
            'commenter_id' => $commenter->getKey(),
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
            'content' => $content,
            'parent_id' => $parentId,
            'status' => CommentStatus::PUBLISHED,
        ]);

        return $this->commentRepository->create($record);
    }

    public function update(int $commentId, string $content): Model
    {
        $existing = $this->commentRepository->find($commentId);

        if (! $existing) {
            throw new RuntimeException(sprintf('Comment %d not found', $commentId));
        }

        $updateRecord = CommentRecord::from(['content' => $content]);

        return $this->commentRepository->update($commentId, $updateRecord);
    }

    public function delete(int $commentId): void
    {
        $existing = $this->commentRepository->find($commentId);

        if (! $existing) {
            throw new RuntimeException(sprintf('Comment %d not found', $commentId));
        }

        $this->commentRepository->delete($commentId);
    }

    public function hide(int $commentId): Model
    {
        $existing = $this->commentRepository->find($commentId);

        if (! $existing) {
            throw new RuntimeException(sprintf('Comment %d not found', $commentId));
        }

        $updateRecord = CommentRecord::from(['status' => CommentStatus::HIDDEN]);

        return $this->commentRepository->update($commentId, $updateRecord);
    }

    public function publish(int $commentId): Model
    {
        $existing = $this->commentRepository->find($commentId);

        if (! $existing) {
            throw new RuntimeException(sprintf('Comment %d not found', $commentId));
        }

        $updateRecord = CommentRecord::from(['status' => CommentStatus::PUBLISHED]);

        return $this->commentRepository->update($commentId, $updateRecord);
    }

    public function flag(int $commentId): Model
    {
        $existing = $this->commentRepository->find($commentId);

        if (! $existing) {
            throw new RuntimeException(sprintf('Comment %d not found', $commentId));
        }

        $updateRecord = CommentRecord::from(['status' => CommentStatus::FLAGGED]);

        return $this->commentRepository->update($commentId, $updateRecord);
    }

    public function get(Model $commentable, bool $onlyPublished = true): Collection
    {
        $filter = CommentFilterRecord::from([
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
            'only_published' => $onlyPublished,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->commentRepository->findBy($findByRecord);
    }

    public function getReplies(int $parentId, bool $onlyPublished = true): Collection
    {
        $filter = CommentFilterRecord::from([
            'parent_id' => $parentId,
            'only_published' => $onlyPublished,
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->commentRepository->findBy($findByRecord);
    }

    public function find(int $commentId): ?Model
    {
        return $this->commentRepository->find($commentId);
    }

    public function getByCommenter(Model $commenter): Collection
    {
        $filter = CommentFilterRecord::from([
            'commenter_type' => $commenter->getMorphClass(),
            'commenter_id' => $commenter->getKey(),
        ]);

        $findByRecord = new FindByRecord(filters: $filter);

        return $this->commentRepository->findBy($findByRecord);
    }

    public function count(Model $commentable, bool $onlyPublished = true): int
    {
        $filter = CommentFilterRecord::from([
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
            'only_published' => $onlyPublished,
        ]);

        return $this->commentRepository->count($filter);
    }

    public function countFlagged(): int
    {
        $filter = CommentFilterRecord::from([
            'status' => CommentStatus::FLAGGED,
        ]);

        return $this->commentRepository->count($filter);
    }

    public function countHidden(): int
    {
        $filter = CommentFilterRecord::from([
            'status' => CommentStatus::HIDDEN,
        ]);

        return $this->commentRepository->count($filter);
    }

    public function countPublished(): int
    {
        $filter = CommentFilterRecord::from([
            'status' => CommentStatus::PUBLISHED,
        ]);

        return $this->commentRepository->count($filter);
    }
}

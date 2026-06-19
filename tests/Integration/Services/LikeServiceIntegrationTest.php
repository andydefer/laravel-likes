<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Tests\Integration\Services;

use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\LaravelLikes\Repositories\LikeRepository;
use AndyDefer\LaravelLikes\Services\LikeService;
use AndyDefer\LaravelLikes\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelLikes\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelLikes\Tests\IntegrationTestCase;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use Illuminate\Support\Collection;
use RuntimeException;

final class LikeServiceIntegrationTest extends IntegrationTestCase
{
    private LikeService $likeService;

    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->likeService = new LikeService(
            new LikeRepository
        );

        $this->user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->post = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Test Post',
            'body' => 'Test content',
        ]);
    }

    public function test_toggle_adds_like_when_not_exists(): void
    {
        $result = $this->likeService->toggle($this->user, $this->post);

        $this->assertTrue($result);
        $this->assertTrue($this->likeService->hasLiked($this->user, $this->post));
        $this->assertSame(1, $this->likeService->countLikes($this->post));
    }

    public function test_toggle_removes_like_when_exists(): void
    {
        $this->likeService->toggle($this->user, $this->post);
        $result = $this->likeService->toggle($this->user, $this->post);

        $this->assertFalse($result);
        $this->assertFalse($this->likeService->hasLiked($this->user, $this->post));
        $this->assertSame(0, $this->likeService->countLikes($this->post));
    }

    public function test_toggle_with_different_type(): void
    {
        $result = $this->likeService->toggle($this->user, $this->post, LikeType::LOVE);

        $this->assertTrue($result);
        $this->assertTrue($this->likeService->hasLiked($this->user, $this->post));

        $likes = $this->likeService->getLikerLikes($this->user);
        $this->assertSame(LikeType::LOVE, $likes->first()->type);
    }

    public function test_like_adds_like_when_not_exists(): void
    {
        $this->likeService->like($this->user, $this->post);

        $this->assertTrue($this->likeService->hasLiked($this->user, $this->post));
        $this->assertSame(1, $this->likeService->countLikes($this->post));
    }

    public function test_like_throws_exception_when_already_liked(): void
    {
        $this->likeService->like($this->user, $this->post);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has already liked');

        $this->likeService->like($this->user, $this->post);
    }

    public function test_toggle_changes_type_when_different_type(): void
    {
        $this->likeService->toggle($this->user, $this->post, LikeType::LOVE);
        $this->likeService->toggle($this->user, $this->post, LikeType::LIKE);

        $likes = $this->likeService->getLikerLikes($this->user);
        $this->assertSame(LikeType::LIKE, $likes->first()->type);
        $this->assertSame(1, $this->likeService->countLikes($this->post));
    }

    public function test_unlike_removes_like_when_exists(): void
    {
        $this->likeService->like($this->user, $this->post);
        $this->likeService->unlike($this->user, $this->post);

        $this->assertFalse($this->likeService->hasLiked($this->user, $this->post));
        $this->assertSame(0, $this->likeService->countLikes($this->post));
    }

    public function test_unlike_throws_exception_when_not_liked(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has not liked');

        $this->likeService->unlike($this->user, $this->post);
    }

    public function test_has_liked_returns_false_when_no_like(): void
    {
        $result = $this->likeService->hasLiked($this->user, $this->post);

        $this->assertFalse($result);
    }

    public function test_has_liked_returns_true_when_like_exists(): void
    {
        $this->likeService->like($this->user, $this->post);
        $result = $this->likeService->hasLiked($this->user, $this->post);

        $this->assertTrue($result);
    }

    public function test_count_likes_returns_zero_when_no_likes(): void
    {
        $count = $this->likeService->countLikes($this->post);

        $this->assertSame(0, $count);
    }

    public function test_count_likes_returns_correct_number(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->likeService->like($this->user, $this->post);
        $this->likeService->like($user2, $this->post);

        $count = $this->likeService->countLikes($this->post);

        $this->assertSame(2, $count);
    }

    public function test_count_likes_by_type_returns_correct_number(): void
    {
        $this->likeService->toggle($this->user, $this->post, LikeType::LOVE);

        $loveCount = $this->likeService->countLikesByType($this->post, LikeType::LOVE);
        $likeCount = $this->likeService->countLikesByType($this->post, LikeType::LIKE);

        $this->assertSame(1, $loveCount);
        $this->assertSame(0, $likeCount);
    }

    public function test_get_likers_returns_collection_of_likers(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->likeService->like($this->user, $this->post);
        $this->likeService->like($user2, $this->post);

        $likers = $this->likeService->getLikers($this->post);

        $this->assertInstanceOf(Collection::class, $likers);
        $this->assertCount(2, $likers);
    }

    public function test_get_likers_by_type_returns_filtered_likers(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->likeService->toggle($this->user, $this->post, LikeType::LOVE);
        $this->likeService->toggle($user2, $this->post, LikeType::LIKE);

        $loveLikers = $this->likeService->getLikersByType($this->post, LikeType::LOVE);
        $likeLikers = $this->likeService->getLikersByType($this->post, LikeType::LIKE);

        $this->assertCount(1, $loveLikers);
        $this->assertCount(1, $likeLikers);
    }

    public function test_get_liker_likes_returns_collection_of_user_likes(): void
    {
        $post2 = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Second Post',
            'body' => 'Another content',
        ]);

        $this->likeService->like($this->user, $this->post);
        $this->likeService->like($this->user, $post2);

        $likes = $this->likeService->getLikerLikes($this->user);

        $this->assertInstanceOf(Collection::class, $likes);
        $this->assertCount(2, $likes);
    }

    public function test_get_liker_likes_by_type_returns_filtered_likes(): void
    {
        $post2 = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Second Post',
            'body' => 'Another content',
        ]);

        $this->likeService->toggle($this->user, $this->post, LikeType::LOVE);
        $this->likeService->toggle($this->user, $post2, LikeType::LIKE);

        $loveLikes = $this->likeService->getLikerLikesByType($this->user, LikeType::LOVE);
        $likeLikes = $this->likeService->getLikerLikesByType($this->user, LikeType::LIKE);

        $this->assertCount(1, $loveLikes);
        $this->assertCount(1, $likeLikes);
    }

    public function test_get_likes_updated_after_returns_likes_updated_after_date(): void
    {
        $this->likeService->like($this->user, $this->post);

        $pastDate = DateTimeVO::from(now()->subDay()->toIso8601String());

        $pastLikes = $this->likeService->getLikesUpdatedAfter($pastDate);

        $this->assertCount(1, $pastLikes);
    }

    public function test_get_liker_likes_updated_after_returns_filtered_likes(): void
    {
        $this->likeService->like($this->user, $this->post);

        $pastDate = DateTimeVO::from(now()->subDay()->toIso8601String());

        $pastLikes = $this->likeService->getLikerLikesUpdatedAfter($this->user, $pastDate);

        $this->assertCount(1, $pastLikes);
    }

    public function test_get_likes_for_likeable_updated_after_returns_filtered_likes(): void
    {
        $this->likeService->like($this->user, $this->post);

        $pastDate = DateTimeVO::from(now()->subDay()->toIso8601String());

        $pastLikes = $this->likeService->getLikesForLikeableUpdatedAfter($this->post, $pastDate);

        $this->assertCount(1, $pastLikes);
    }
}

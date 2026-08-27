<?php

// tests/Integration/Casts/LikeCastTest.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Tests\Integration\Casts;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;
use AndyDefer\LaravelLikes\Enums\LikeType;
use AndyDefer\LaravelLikes\Models\Like;
use AndyDefer\LaravelLikes\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelLikes\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelLikes\Tests\IntegrationTestCase;

final class LikeCastTest extends IntegrationTestCase
{
    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_cast_returns_like_type_enum_from_string(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => 'love',
        ]);

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertInstanceOf(LikeType::class, $fresh->type);
        $this->assertSame(LikeType::LOVE, $fresh->type);
        $this->assertSame('love', $fresh->type->getValue());
        $this->assertSame('❤️', $fresh->type->getEmoji());
        $this->assertSame('J\'adore', $fresh->type->getLabel());
    }

    public function test_cast_handles_like_type(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::LIKE,
        ]);

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(LikeType::LIKE, $fresh->type);
        $this->assertSame('like', $fresh->type->getValue());
        $this->assertSame('👍', $fresh->type->getEmoji());
        $this->assertSame('J\'aime', $fresh->type->getLabel());
    }

    public function test_cast_handles_all_like_types(): void
    {
        $types = [
            'like' => ['emoji' => '👍', 'label' => 'J\'aime'],
            'love' => ['emoji' => '❤️', 'label' => 'J\'adore'],
            'haha' => ['emoji' => '😂', 'label' => 'Haha'],
            'wow' => ['emoji' => '😮', 'label' => 'Wow'],
            'sad' => ['emoji' => '😢', 'label' => 'Triste'],
            'angry' => ['emoji' => '😡', 'label' => 'En colère'],
        ];

        $i = 0;
        foreach ($types as $typeValue => $expected) {
            $i++;

            $like = Like::create([
                'liker_type' => TestUser::class,
                'liker_id' => $this->user->id + $i,
                'likeable_type' => TestPost::class,
                'likeable_id' => $this->post->id,
                'type' => $typeValue,
            ]);

            $fresh = Like::find($like->id);

            $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
            $this->assertSame($typeValue, $fresh->type->getValue());
            $this->assertSame($expected['emoji'], $fresh->type->getEmoji());
            $this->assertSame($expected['label'], $fresh->type->getLabel());
        }
    }

    public function test_stores_enum_as_string_in_database(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::HAHA,
        ]);

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'haha',
        ]);

        $raw = Like::find($like->id)->getAttributes();
        $this->assertIsString($raw['type']);
        $this->assertSame('haha', $raw['type']);
    }

    public function test_stores_string_value_in_database(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => 'wow',
        ]);

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'wow',
        ]);

        $raw = Like::find($like->id)->getAttributes();
        $this->assertIsString($raw['type']);
        $this->assertSame('wow', $raw['type']);
    }

    public function test_handles_default_value(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
        ]);

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(LikeType::LIKE, $fresh->type);
        $this->assertSame('like', $fresh->type->getValue());
        $this->assertSame('👍', $fresh->type->getEmoji());
        $this->assertSame('J\'aime', $fresh->type->getLabel());
    }

    public function test_update_type_via_enum(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::LIKE,
        ]);

        $like->type = LikeType::LOVE;
        $like->save();

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(LikeType::LOVE, $fresh->type);
        $this->assertSame('love', $fresh->type->getValue());
        $this->assertSame('❤️', $fresh->type->getEmoji());
        $this->assertSame('J\'adore', $fresh->type->getLabel());

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'love',
        ]);
    }

    public function test_update_type_via_string(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::LIKE,
        ]);

        $like->type = 'haha';
        $like->save();

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(LikeType::HAHA, $fresh->type);
        $this->assertSame('haha', $fresh->type->getValue());
        $this->assertSame('😂', $fresh->type->getEmoji());
        $this->assertSame('Haha', $fresh->type->getLabel());

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'haha',
        ]);
    }

    public function test_cast_works_with_relations(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::WOW,
        ]);

        $fresh = Like::with('liker', 'likeable')->find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(LikeType::WOW, $fresh->type);
        $this->assertSame('wow', $fresh->type->getValue());
        $this->assertSame('😮', $fresh->type->getEmoji());
        $this->assertSame('Wow', $fresh->type->getLabel());

        $this->assertNotNull($fresh->liker);
        $this->assertEquals($this->user->id, $fresh->liker->id);
        $this->assertNotNull($fresh->likeable);
        $this->assertEquals($this->post->id, $fresh->likeable->id);
    }

    public function test_cast_with_multiple_records(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $post2 = TestPost::create([
            'user_id' => $user2->id,
            'title' => 'Test Post 2',
            'body' => 'Another test content',
        ]);

        Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::LIKE,
        ]);

        Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $user2->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::LOVE,
        ]);

        Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $post2->id,
            'type' => LikeType::HAHA,
        ]);

        $likes = Like::all();

        $this->assertCount(3, $likes);

        $types = $likes->pluck('type')->map(fn ($type) => $type->getValue())->toArray();
        $this->assertContains('like', $types);
        $this->assertContains('love', $types);
        $this->assertContains('haha', $types);

        foreach ($likes as $like) {
            $this->assertInstanceOf(LikeTypeInterface::class, $like->type);
        }
    }

    public function test_cast_with_metadata_preserves_data(): void
    {
        $metadata = ['ip' => '192.168.1.1', 'device' => 'mobile', 'browser' => 'Chrome'];

        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => LikeType::LIKE,
            'metadata' => $metadata,
        ]);

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(LikeType::LIKE, $fresh->type);

        $this->assertInstanceOf(StrictDataObject::class, $fresh->metadata);
        $this->assertEquals($metadata, $fresh->metadata->toArray());
    }

    public function test_cast_performance_with_many_records(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $user = TestUser::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
            ]);

            $post = TestPost::create([
                'user_id' => $user->id,
                'title' => "Post $i",
                'body' => "Content $i",
            ]);

            Like::create([
                'liker_type' => TestUser::class,
                'liker_id' => $user->id,
                'likeable_type' => TestPost::class,
                'likeable_id' => $post->id,
                'type' => LikeType::cases()[array_rand(LikeType::cases())],
            ]);
        }

        $start = microtime(true);

        $likes = Like::all();

        foreach ($likes as $like) {
            $this->assertInstanceOf(LikeTypeInterface::class, $like->type);
        }

        $end = microtime(true);
        $time = ($end - $start) * 1000;

        $this->assertLessThan(100, $time);
    }
}

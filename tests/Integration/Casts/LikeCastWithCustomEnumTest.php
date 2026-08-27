<?php

// tests/Integration/Casts/LikeCastWithCustomEnumTest.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes\Tests\Integration\Casts;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelLikes\Configs\LikeConfig;
use AndyDefer\LaravelLikes\Contracts\Configs\LikeConfigInterface;
use AndyDefer\LaravelLikes\Contracts\LikeTypeInterface;
use AndyDefer\LaravelLikes\Models\Like;
use AndyDefer\LaravelLikes\Tests\Fixtures\Enums\TestLikeType;
use AndyDefer\LaravelLikes\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelLikes\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelLikes\Tests\IntegrationTestCase;
use Illuminate\Support\Facades\Config;

final class LikeCastWithCustomEnumTest extends IntegrationTestCase
{
    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ Enregistrer le nouvel enum dans la config
        Config::set('likes.like_type_enum', TestLikeType::class);

        // ✅ Rebinder l'interface avec la nouvelle config
        $this->app->singleton(LikeConfig::class, function ($app) {
            return new LikeConfig($app['config']);
        });

        $this->app->bind(LikeConfigInterface::class, LikeConfig::class);

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

    public function test_cast_returns_custom_like_type_from_string(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => 'fire',
        ]);

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertInstanceOf(TestLikeType::class, $fresh->type);
        $this->assertSame(TestLikeType::FIRE, $fresh->type);
        $this->assertSame('fire', $fresh->type->getValue());
        $this->assertSame('🔥', $fresh->type->getEmoji());
        $this->assertSame('En feu !', $fresh->type->getLabel());
    }

    public function test_cast_handles_custom_like_type(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => TestLikeType::ROCKET,
        ]);

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(TestLikeType::ROCKET, $fresh->type);
        $this->assertSame('rocket', $fresh->type->getValue());
        $this->assertSame('🚀', $fresh->type->getEmoji());
        $this->assertSame('Génial !', $fresh->type->getLabel());
    }

    public function test_cast_handles_all_custom_like_types(): void
    {
        $types = [
            'fire' => ['emoji' => '🔥', 'label' => 'En feu !'],
            'rocket' => ['emoji' => '🚀', 'label' => 'Génial !'],
            'heart' => ['emoji' => '💖', 'label' => 'Adorable !'],
            'clap' => ['emoji' => '👏', 'label' => 'Bravo !'],
            'star' => ['emoji' => '⭐', 'label' => 'Super !'],
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

    public function test_stores_custom_enum_as_string_in_database(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => TestLikeType::HEART,
        ]);

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'heart',
        ]);

        $raw = Like::find($like->id)->getAttributes();
        $this->assertIsString($raw['type']);
        $this->assertSame('heart', $raw['type']);
    }

    public function test_stores_custom_string_value_in_database(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => 'clap',
        ]);

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'clap',
        ]);

        $raw = Like::find($like->id)->getAttributes();
        $this->assertIsString($raw['type']);
        $this->assertSame('clap', $raw['type']);
    }

    public function test_update_type_via_custom_enum(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => TestLikeType::FIRE,
        ]);

        $like->type = TestLikeType::STAR;
        $like->save();

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(TestLikeType::STAR, $fresh->type);
        $this->assertSame('star', $fresh->type->getValue());
        $this->assertSame('⭐', $fresh->type->getEmoji());
        $this->assertSame('Super !', $fresh->type->getLabel());

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'star',
        ]);
    }

    public function test_update_type_via_custom_string(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => TestLikeType::FIRE,
        ]);

        $like->type = 'rocket';
        $like->save();

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(TestLikeType::ROCKET, $fresh->type);
        $this->assertSame('rocket', $fresh->type->getValue());
        $this->assertSame('🚀', $fresh->type->getEmoji());
        $this->assertSame('Génial !', $fresh->type->getLabel());

        $this->assertDatabaseHas('likes', [
            'id' => $like->id,
            'type' => 'rocket',
        ]);
    }

    public function test_cast_works_with_custom_enum_and_relations(): void
    {
        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => TestLikeType::CLAP,
        ]);

        $fresh = Like::with('liker', 'likeable')->find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(TestLikeType::CLAP, $fresh->type);
        $this->assertSame('clap', $fresh->type->getValue());
        $this->assertSame('👏', $fresh->type->getEmoji());
        $this->assertSame('Bravo !', $fresh->type->getLabel());

        $this->assertNotNull($fresh->liker);
        $this->assertEquals($this->user->id, $fresh->liker->id);
        $this->assertNotNull($fresh->likeable);
        $this->assertEquals($this->post->id, $fresh->likeable->id);
    }

    public function test_cast_with_custom_enum_and_metadata(): void
    {
        $metadata = ['ip' => '192.168.1.1', 'device' => 'mobile'];

        $like = Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => TestLikeType::HEART,
            'metadata' => $metadata,
        ]);

        $fresh = Like::find($like->id);

        $this->assertInstanceOf(LikeTypeInterface::class, $fresh->type);
        $this->assertSame(TestLikeType::HEART, $fresh->type);
        $this->assertSame('heart', $fresh->type->getValue());
        $this->assertSame('💖', $fresh->type->getEmoji());
        $this->assertSame('Adorable !', $fresh->type->getLabel());

        $this->assertInstanceOf(StrictDataObject::class, $fresh->metadata);
        $this->assertEquals($metadata, $fresh->metadata->toArray());
    }

    public function test_cast_with_custom_enum_and_multiple_records(): void
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
            'type' => TestLikeType::FIRE,
        ]);

        Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $user2->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $this->post->id,
            'type' => TestLikeType::STAR,
        ]);

        Like::create([
            'liker_type' => TestUser::class,
            'liker_id' => $this->user->id,
            'likeable_type' => TestPost::class,
            'likeable_id' => $post2->id,
            'type' => TestLikeType::CLAP,
        ]);

        $likes = Like::all();

        $this->assertCount(3, $likes);

        $types = $likes->pluck('type')->map(fn ($type) => $type->getValue())->toArray();
        $this->assertContains('fire', $types);
        $this->assertContains('star', $types);
        $this->assertContains('clap', $types);

        foreach ($likes as $like) {
            $this->assertInstanceOf(LikeTypeInterface::class, $like->type);
        }
    }

    public function test_cast_performance_with_custom_enum_and_many_records(): void
    {
        $customTypes = TestLikeType::cases();

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
                'type' => $customTypes[array_rand($customTypes)],
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

<?php

// src/LikesServiceProvider.php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes;

use AndyDefer\LaravelLikes\Contracts\Repositories\LikeRepositoryInterface;
use AndyDefer\LaravelLikes\Contracts\Services\LikeServiceInterface;
use AndyDefer\LaravelLikes\Repositories\LikeRepository;
use AndyDefer\LaravelLikes\Services\LikeService;
use Illuminate\Support\ServiceProvider;

final class LikesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register concrete classes
        $this->app->singleton(LikeRepository::class, function ($app) {
            return new LikeRepository;
        });

        $this->app->singleton(LikeService::class, function ($app) {
            return new LikeService(
                $app->make(LikeRepositoryInterface::class)
            );
        });

        // Bind interfaces to concrete classes
        $this->app->bind(LikeRepositoryInterface::class, LikeRepository::class);
        $this->app->bind(LikeServiceInterface::class, LikeService::class);
    }

    public function boot(): void
    {
        // Migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Publishes
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'likes-migrations');
    }
}

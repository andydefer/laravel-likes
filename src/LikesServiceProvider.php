<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes;

use AndyDefer\LaravelLikes\Configs\LikeConfig;
use AndyDefer\LaravelLikes\Contracts\Configs\LikeConfigInterface;
use AndyDefer\LaravelLikes\Repositories\LikeRepository;
use AndyDefer\LaravelLikes\Services\LikeService;
use Illuminate\Support\ServiceProvider;

final class LikesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Config
        $this->mergeConfigFrom(
            __DIR__.'/../config/likes.php',
            'likes'
        );

        // Bindings
        $this->app->singleton(LikeConfig::class, function ($app) {
            return new LikeConfig($app['config']);
        });

        $this->app->bind(LikeConfigInterface::class, LikeConfig::class);

        $this->app->singleton(LikeRepository::class);
        $this->app->singleton(LikeService::class);
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

        $this->publishes([
            __DIR__.'/../config/likes.php' => config_path('likes.php'),
        ], 'likes-config');
    }
}

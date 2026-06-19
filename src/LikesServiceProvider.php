<?php

declare(strict_types=1);

namespace AndyDefer\LaravelLikes;

use AndyDefer\LaravelLikes\Repositories\LikeRepository;
use AndyDefer\LaravelLikes\Services\LikeService;
use Illuminate\Support\ServiceProvider;

final class LikesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LikeRepository::class);
        $this->app->singleton(LikeService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/migrations');
        }

        $this->publishes([
            __DIR__.'/migrations' => database_path('migrations'),
        ], 'Likes-migrations');
    }
}

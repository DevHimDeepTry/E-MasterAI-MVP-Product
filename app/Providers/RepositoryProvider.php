<?php

namespace App\Providers;

use App\Repositories\Implements\GeminiRepository;
use App\Repositories\Interfaces\IGeminiRepository;
use Carbon\Laravel\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(
            IGeminiRepository::class,
            GeminiRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

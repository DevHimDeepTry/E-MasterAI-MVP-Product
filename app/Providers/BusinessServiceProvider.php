<?php

namespace App\Providers;

use App\Services\Implements\MVPService;
use App\Services\Interfaces\IMVPService;
use Carbon\Laravel\ServiceProvider;

class BusinessServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(
            IMVPService::class,
            MVPService::class
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

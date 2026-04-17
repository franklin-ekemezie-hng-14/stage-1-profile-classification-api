<?php

namespace App\Providers;

use App\Repositories\EloquentProfileRepository;
use App\Repositories\ProfileRepositoryInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

        $this->app->bind(
            ProfileRepositoryInterface::class,
            EloquentProfileRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

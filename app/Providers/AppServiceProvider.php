<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('module', function ($app) {
            return new Module();
        });
       
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen('eloquent.saved: *', function () {
            Cache::flush(); // Clear the cache on create or update
        });
    
        Event::listen('eloquent.deleted: *', function () {
            Cache::flush(); // Clear the cache on delete
        });
    }
}

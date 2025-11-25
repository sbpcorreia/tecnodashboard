<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Routing\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(!config('app.debug')) {
            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/tecnodashboard/livewire/update', $handle);
            });
            Livewire::setScriptRoute(function ($handle) {
                return Route::get('/tecnodashboard/livewire/livewire.js', $handle);
            });
        }
    }
}

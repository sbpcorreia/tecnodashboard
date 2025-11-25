<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


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

        URL::forceRootUrl(config('app.url'));

        // Se estiver atrás de proxy
        if (request()->server->has('HTTP_X_FORWARDED_HOST')) {
            URL::forceScheme('https');
        }

        if(!config('app.debug')) {
            Livewire::setScriptRoute(function($handle) {
                return Route::post('/tecnodashboard/vendor/livewire/livewire.js', $handle);
            });

            Livewire::setUpdateRoute(function($handle) {
                return Route::post('/tecnodashboard/livewire/update', $handle);
            });

            // Forçar URL base
            //URL::forceRootUrl(config('app.url'));
            //
            //// IMPORTANTE: Configurar o Livewire para usar o endpoint correto
            //Livewire::setUpdateRoute(function ($handle) {
            //    return \Illuminate\Support\Facades\Route::post('/tecnodashboard/livewire/update', $handle)
            //        ->middleware('web')
            //        ->name('livewire.update');
            //});
        //
            //Livewire::setScriptRoute(function ($handle) {
            //    return \Illuminate\Support\Facades\Route::get('/tecnodashboard/livewire/livewire.min.js', $handle)
            //        ->name('livewire.javascript');
            //});
        }
    }
}

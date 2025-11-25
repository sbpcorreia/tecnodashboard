<?php


use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Livewire\WorkCenter;
use Livewire\Livewire;

Route::get('/', WorkCenter::class);

$livewireUpdateRoute = env('LIVEWIRE_UPDATE_ROUTE');

// Set the Livewire update route dynamically
Livewire::setUpdateRoute(function ($handle) use ($livewireUpdateRoute) {
return Route::post($livewireUpdateRoute, $handle);
});
//Route::get('/test', [Test::class, "showAll"]);

/*Route::get('/', function () {
    return view('welcome');
})->name('home');*/

/*Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});*/

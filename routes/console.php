<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Executa a cada 10 segundos, sem sobreposição (para não encravar se o anterior demorar)
Schedule::command('app:check-work-centers-status')
        ->everyTenSeconds()
        ->withoutOverlapping();

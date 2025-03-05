<?php

use App\Jobs\FetchWeatherJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new FetchWeatherJob())
    ->hourly()
    ->name('fetch-weather-job')
    ->withoutOverlapping()
    ->timezone('Australia/Perth')
    ->onFailure(function () {
        Log::error('FetchWeatherJob failed to execute.');
    })
    ->onSuccess(function () {
        Log::info('FetchWeatherJob executed successfully.');
    });

Artisan::command('app:clean-weather-data', function () {
    $this->call('app:clean-weather-data');
})->purpose('Remove old weather data from the database.');
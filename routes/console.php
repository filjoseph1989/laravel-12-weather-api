<?php

use App\Jobs\FetchWeatherJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Cleaning old weather data every day at midnight
Artisan::command('app:clean-weather-data', function () {
    $this->call('app:clean-weather-data');
})->purpose('Remove old weather data from the database.')->daily();

// Fetching weather data every hour
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
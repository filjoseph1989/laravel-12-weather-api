<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/weather', [WeatherController::class, 'getWeather'])->name('current_weather');
Route::middleware('auth:sanctum')->get('/posts', [PostController::class, 'allPost'])->name('allPost');
Route::post('/login', [AuthController::class, 'login'])->name('login');

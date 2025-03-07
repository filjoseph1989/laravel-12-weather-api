<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

// Endpoints that don't requires authentication
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Endpoints that requires authentication
Route::middleware('auth:sanctum')->group( function () {
    // Weather endpoints
    Route::get('/weather', [WeatherController::class, 'getWeather'])->name('current_weather');

    // Post endpoints
    Route::get('/posts', [PostController::class, 'getPaginatedPost'])->name('getPaginatedPost');
    Route::get('/posts/{post}', [PostController::class, 'getPost'])->name('getPost');
    Route::post('/posts', [PostController::class, 'store'])->name('createPost');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('updatePost');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('deletePost');

    // User endpoints
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/users/{user}', [UserController::class, 'getUser'])->name('getUser');
});

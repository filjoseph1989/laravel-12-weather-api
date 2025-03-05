<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->get('/weather', [WeatherController::class, 'getWeather'])->name('current_weather');

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/posts', [PostController::class, 'getPaginatedPost'])->name('getPaginatedPost');
    Route::get('/posts/{post}', [PostController::class, 'getPost'])->name('getPost');
    Route::post('/posts', [PostController::class, 'store'])->name('createPost');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('updatePost');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('deletePost');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/users/{user}', [UserController::class, 'getUser'])->name('getUser');
});

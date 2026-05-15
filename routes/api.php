<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\Admin\AdminEbookController;

Route::prefix('v1')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [RegisterController::class, 'register']);
        Route::post('login', [LoginController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [LoginController::class, 'logout']);
            Route::get('me', [LoginController::class, 'me']);
        });
    });

    // Public ebook routes — anyone can browse
    Route::prefix('ebooks')->group(function () {
        Route::get('/', [EbookController::class, 'index']);
        Route::get('{slug}', [EbookController::class, 'show']);
    });

    // Admin ebook routes — must be logged in AND be an admin
    Route::prefix('admin')->middleware(['auth:sanctum', 'auth.admin'])->group(function () {
        Route::apiResource('ebooks', AdminEbookController::class);
    });

});
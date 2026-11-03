<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes with JWT Authentication
|--------------------------------------------------------------------------
*/

// Public Authentication endpoints
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

// Protected Routes (Guarded with jwt.auth middleware)
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.refresh');
    Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
});

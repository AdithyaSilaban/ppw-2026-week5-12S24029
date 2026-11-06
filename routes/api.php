<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthTokenController;

Route::prefix('v1')->group(function () {
    // 1. Endpoint Publik (Dapat diakses tanpa token)
    Route::post('/auth/login', [AuthTokenController::class, 'login']);

    // 2. Endpoint Terproteksi Stateless Bearer Token (Wajib auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);
    });
});
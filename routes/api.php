<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/phone/verify', [AuthController::class, 'verifyPhone'])
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/phone/otp', [AuthController::class, 'requestPhoneOtp']);
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
    });
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::apiResource('services', ServiceController::class)->except(['create', 'edit']);
});

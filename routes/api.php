<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
        //régénère un OTP si le numéro n’est pas encore vérifié.
        Route::post('/phone/otp', [AuthController::class, 'requestPhoneOtp']);
        //Retourne les infos du profil associé au token.
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
    });
});

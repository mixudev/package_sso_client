<?php

use Illuminate\Support\Facades\Route;
use Mixu\SSOAuth\Http\Controllers\Auth\AuthController;
use Mixu\SSOAuth\Http\Controllers\SsoLogoutCallbackController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// SSO Auth Routes - All prefixed with /auth for consistency
Route::prefix('auth')->name('auth.')->group(function () {
    // Login redirect ke SSO Server
    Route::get('/login', [AuthController::class, 'redirect'])
        ->name('login')
        ->middleware('throttle:20,1');

    // Callback dari SSO Server
    Route::get('/callback', [AuthController::class, 'callback'])
        ->name('callback');
    
    // Logout dari aplikasi
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout')
        ->middleware('throttle:10,1');
});

// Global Logout webhook (POST dari SSO Server) - tanpa CSRF protection
// Verifikasi via HMAC signature dari webhook secret

<?php

use Illuminate\Support\Facades\Route;
use Mixu\SSOAuth\Http\Controllers\Auth\AuthController;
use Mixu\SSOAuth\Http\Controllers\SsoLogoutCallbackController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Auth SSO
Route::name('auth.')->group(function () {
    // Login: rate limit untuk keamanan
    Route::get('/login', [AuthController::class, 'redirect'])
        ->name('login')
        ->middleware('throttle:20,1');

    // Callback: tidak perlu rate limit ketat karena ini redirect dari SSO (legitimate)
    Route::get('/auth/callback', [AuthController::class, 'callback'])
        ->name('callback');
    
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout')
        ->middleware('throttle:10,1');
});

// Global Logout webhook (dari SSO Server) - tanpa CSRF, verifikasi via HMAC signature
Route::post('/auth/sso/logout-callback', [SsoLogoutCallbackController::class, 'handle'])
    ->name('sso.logout-callback')
    ->withoutMiddleware([ValidateCsrfToken::class]);

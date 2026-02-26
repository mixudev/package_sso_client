<?php

use Illuminate\Support\Facades\Route;
use Mixu\SSOAuth\Http\Controllers\Auth\AuthController;
use Mixu\SSOAuth\Http\Controllers\SsoLogoutCallbackController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Mixu\SSOAuth\Http\Controllers\SecurityController;

// Auth SSO
Route::get('/login', [AuthController::class, 'redirect'])
    ->name('login')
    ->middleware('throttle:20,1');

// Callback
Route::get('/auth/callback', [AuthController::class, 'callback'])
    ->name('auth.callback');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('throttle:10,1');

// Global Logout webhook (dari SSO Server)  
Route::post('/auth/sso/logout-callback', [SsoLogoutCallbackController::class, 'handle'])
    ->name('sso.logout-callback')
    ->withoutMiddleware([ValidateCsrfToken::class]);

Route::middleware([
    'sso.auth', 
    'sso.alive', 
    'validate.session.ip', 
    // 'track.activity', 
    'audit.trail'
])->group(function () {
    // Security monitoring routes
    Route::get('/security', [SecurityController::class, 'dashboard'])
        ->name('security.dashboard');
        
    Route::get('/security/page-access', [SecurityController::class, 'pageAccessLogs'])
        ->name('security.page-access');

    Route::get('/security/events', [SecurityController::class, 'securityEvents'])
        ->name('security.events');

    Route::get('/security/audit', [SecurityController::class, 'auditLogs'])
        ->name('security.audit');

    Route::get('/security/user-activity', [SecurityController::class, 'userActivity'])
        ->name('security.user-activity');

    Route::get('/security/check-brute-force', [SecurityController::class, 'checkBruteForce'])
        ->name('security.check-brute-force');

    Route::get('/security/export-logs', [SecurityController::class, 'exportLogs'])
        ->name('security.export-logs');
});
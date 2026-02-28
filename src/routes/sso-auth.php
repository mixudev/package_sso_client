<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

use Mixu\SSOAuth\Http\Controllers\Auth\AuthController;
use Mixu\SSOAuth\Http\Controllers\SsoLogoutCallbackController;
use Mixu\SSOAuth\Http\Controllers\SecurityController;
use Mixu\SSOAuth\Http\Controllers\LogDeletionController;
use Mixu\SSOAuth\Http\Controllers\LogExportController;
use Mixu\SSOAuth\Http\Controllers\SecurityNotificationController;

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES 
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'redirect'])
    ->name('login')
    ->middleware('throttle:20,1');

Route::get('/auth/callback', [AuthController::class, 'callback'])
    ->name('auth.callback');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('throttle:10,1', 'track.activity', 'audit.trail');


/*
|--------------------------------------------------------------------------
| SSO WEBHOOK (SYSTEM ROUTE)
|--------------------------------------------------------------------------
*/

Route::post('/auth/sso/logout-callback',
    [SsoLogoutCallbackController::class, 'handle']
)
->name('sso.logout-callback')
->withoutMiddleware([ValidateCsrfToken::class]);



/*
|--------------------------------------------------------------------------
| PROTECTED APPLICATION
|--------------------------------------------------------------------------
*/

Route::middleware([
    'sso.auth',
    'sso.alive',
    'validate.session.ip',
])->group(function () {

    /*
    | Dashboard
    */
    Route::get('/dashboard', fn () => view('dashboard'))
        ->name('dashboard')
        ->middleware(['track.activity', 'audit.trail']);


    /*
    |--------------------------------------------------------------------------
    | SECURITY MODULE
    |--------------------------------------------------------------------------
    */

    Route::prefix('security')
        ->name('security.')
        ->middleware(['track.activity', 'audit.trail'])
        ->group(function () {

        /*
        | Monitoring
        */
        Route::controller(SecurityController::class)->group(function () {

            Route::get('/', 'dashboard')->name('dashboard');
            Route::get('/page-access', 'pageAccessLogs')->name('page-access');
            Route::get('/events', 'securityEvents')->name('events');
            Route::get('/audit', 'auditLogs')->name('audit');

            Route::get('/user-activity', 'userActivity')
                ->name('user-activity');

            Route::get('/check-brute-force', 'checkBruteForce')
                ->name('check-brute-force');
        });


        /*
        |--------------------------------------------------------------------------
        | EXPORT
        |--------------------------------------------------------------------------
        */
            
        Route::prefix('export-logs')
            ->controller(LogExportController::class)
            ->group(function () {
                Route::get('/', 'index')->name('export-logs');
                Route::get('/download', 'download')->name('export-logs.download');
        });


        /*
        |--------------------------------------------------------------------------
        | LOG DELETION
        |--------------------------------------------------------------------------
        */

        Route::controller(LogDeletionController::class)
            ->prefix('logs')
            ->group(function () {

            // Audit
            Route::prefix('audit')->name('audit.')->group(function () {
                Route::post('{id}/delete', 'deleteAuditLog')->name('delete');
                Route::post('delete-range', 'deleteAuditLogsRange')->name('delete-range');
                Route::post('delete-day', 'deleteAuditLogsDay')->name('delete-day');
                Route::post('delete-all', 'deleteAuditLogsAll')->name('delete-all');
            });

            // Page Access
            Route::prefix('page-access')->name('page-access.')->group(function () {
                Route::post('{id}/delete', 'deletePageAccessLog')->name('delete');
                Route::post('delete-range', 'deletePageAccessLogsRange')->name('delete-range');
                Route::post('delete-day', 'deletePageAccessLogsDay')->name('delete-day');
                Route::post('delete-all', 'deletePageAccessLogsAll')->name('delete-all');
            });

            // Events
            Route::prefix('events')->name('events.')->group(function () {
                Route::post('{id}/delete', 'deleteSecurityEvent')->name('delete');
                Route::post('delete-range', 'deleteSecurityEventsRange')->name('delete-range');
                Route::post('delete-day', 'deleteSecurityEventsDay')->name('delete-day');
                Route::post('delete-all', 'deleteSecurityEventsAll')->name('delete-all');
            });
        });


        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */

        Route::prefix('notifications')
            ->controller(SecurityNotificationController::class)
            ->group(function () {

                Route::get('/', 'index')->name('notifications');
                Route::post('{id}/mark-read', 'markAsRead')->name('notifications.mark-read');
                Route::post('mark-all', 'markAllRead')->name('notifications.mark-all');
        });
    });
});
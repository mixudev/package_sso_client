<?php

namespace Mixu\SSOAuth\Providers;

use Illuminate\Support\ServiceProvider;
use Mixu\SSOAuth\Services\SSOAuthService;
use Mixu\SSOAuth\Services\SecurityMonitoringService;
use Mixu\SSOAuth\Console\Commands\CheckSSOConfig;

class MixuSSOAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Service Configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mixuauth.php',
            'services.mixuauth'
        );

        // Register Services as Singletons
        $this->app->singleton(SSOAuthService::class, function ($app) {
            return new SSOAuthService();
        });

        $this->app->singleton(SecurityMonitoringService::class, function ($app) {
            return new SecurityMonitoringService();
        });

        // Register Facades
        $this->app->alias(SSOAuthService::class, 'sso-auth');
        $this->app->alias(SecurityMonitoringService::class, 'security-monitoring');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckSSOConfig::class,
            ]);
        }

        // Load Routes (sudah ada config dari register())
        $this->loadRoutesFrom(__DIR__ . '/../routes/sso-auth.php');

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mixu-sso-auth');

        // Load Migration Files
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish Configuration
        $this->publishes([
            __DIR__ . '/../config/mixuauth.php' => config_path('mixuauth.php'),
        ], 'mixu-sso-auth-config');

        // Publish Database Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mixu-sso-auth-migrations');

        // Publish Routes
        $this->publishes([
            __DIR__ . '/../routes/sso-auth.php' => base_path('routes/sso-auth.php'),
        ], 'mixu-sso-auth-routes');

        // Publish Views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mixu-sso-auth'),
        ], 'mixu-sso-auth-views');

        // Publish all assets with common tag
        $this->publishes([
            __DIR__ . '/../config/mixuauth.php' => config_path('mixuauth.php'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../routes/sso-auth.php' => base_path('routes/sso-auth.php'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mixu-sso-auth'),
        ], 'mixu-sso-auth');
    }
}


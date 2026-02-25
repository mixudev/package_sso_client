<?php

namespace Mixu\SSOAuth\Console\Commands;

use Illuminate\Console\Command;
use Mixu\SSOAuth\Services\SSOAuthService;

class CheckSSOConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:check {--verbose}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if SSO configuration is properly set up';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking SSO Configuration...');
        $this->newLine();

        // Check environment variables
        $this->info('📋 Environment Variables:');
        $envVars = [
            'AUTH_BASE_URL' => env('AUTH_BASE_URL'),
            'AUTH_CLIENT_ID' => env('AUTH_CLIENT_ID'),
            'AUTH_CLIENT_SECRET' => env('AUTH_CLIENT_SECRET'),
            'AUTH_REDIRECT_URI' => env('AUTH_REDIRECT_URI'),
        ];

        foreach ($envVars as $key => $value) {
            if ($value) {
                $this->line("  ✅ $key = " . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value));
            } else {
                $this->line("  ❌ $key = NOT SET");
            }
        }
        $this->newLine();

        // Check config
        $this->info('⚙️  SSO Configuration (services.mixuauth):');
        $config = config('services.mixuauth', []);
        
        if (empty($config)) {
            $this->error('  ❌ Config empty! Did you run: php artisan vendor:publish --tag=mixu-sso-auth');
            return 1;
        }

        $sso = app(SSOAuthService::class);
        $isConfigured = $sso->isConfigured();

        foreach (['base_url', 'client_id', 'client_secret', 'redirect_uri', 'scopes'] as $key) {
            $value = $config[$key] ?? null;
            if ($value) {
                $display = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                $this->line("  ✅ $key = $display");
            } else {
                $this->line("  ❌ $key = MISSING/EMPTY");
            }
        }
        $this->newLine();

        // Check if fully configured
        if ($isConfigured) {
            $this->info('✅ SSO is fully configured and ready to use!');
            return 0;
        } else {
            $this->error('❌ SSO is NOT fully configured.');
            $this->warn('Please set all required environment variables in .env:');
            $this->table(
                ['Variable', 'Required'],
                [
                    ['AUTH_BASE_URL', 'Yes - SSO Server URL'],
                    ['AUTH_CLIENT_ID', 'Yes - From SSO Server'],
                    ['AUTH_CLIENT_SECRET', 'Yes - From SSO Server'],
                    ['AUTH_REDIRECT_URI', 'Yes - Callback URL (usually http://localhost:8000/auth/callback)'],
                ]
            );
            return 1;
        }
    }
}

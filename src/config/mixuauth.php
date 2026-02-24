<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MixuAuth SSO (Auth Server / Identity Provider)
    |--------------------------------------------------------------------------
    */
    'mixuauth' => [
        'base_url' => rtrim(env('AUTH_BASE_URL', 'https://auth.example.com'), '/'),
        'client_id' => env('AUTH_CLIENT_ID'),
        'client_secret' => env('AUTH_CLIENT_SECRET'),
        'redirect_uri' => env('AUTH_REDIRECT_URI') ?: (rtrim(env('APP_URL', 'http://localhost'), '/') . '/auth/callback'),
        'scopes' => env('AUTH_SCOPES', ''),
        'authorize_url' => '/oauth/authorize',
        'token_url' => '/oauth/token',
        'user_url' => '/api/user',
        'revoke_url' => '/oauth/revoke', // optional: centralized logout
        'webhook_secret' => env('SSO_WEBHOOK_SECRET'), // untuk Global Logout
    ],

];

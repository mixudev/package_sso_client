<?php

namespace Mixu\SSOAuth\Facades;

use Illuminate\Support\Facades\Facade;
use Mixu\SSOAuth\Services\SSOAuthService;

class SSOAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SSOAuthService::class;
    }
}

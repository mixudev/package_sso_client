<?php

namespace Mixu\SSOAuth\Facades;

use Illuminate\Support\Facades\Facade;
use Mixu\SSOAuth\Services\SecurityMonitoringService;

class SecurityMonitoring extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SecurityMonitoringService::class;
    }
}

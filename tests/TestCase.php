<?php

namespace Elsan\JwtCookieGuard\Tests;

use Elsan\JwtCookieGuard\JwtCookieGuardServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider as JwtAuthServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            JwtAuthServiceProvider::class,
            JwtCookieGuardServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('jwt.secret', 'test-secret-not-for-production-use');
    }
}

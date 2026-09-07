<?php

namespace Elsan\JwtCookieGuard;

use Elsan\JwtCookieGuard\Guards\JwtCookieGuard;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class JwtCookieGuardServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::extend('jwtCookie', fn (Application $app, string $name, array $config) => JwtCookieGuard::make($app, $config));
    }
}

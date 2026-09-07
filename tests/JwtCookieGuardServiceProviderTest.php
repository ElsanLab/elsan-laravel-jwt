<?php

namespace Elsan\JwtCookieGuard\Tests;

use Elsan\JwtCookieGuard\Guards\JwtCookieGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;

class JwtCookieGuardServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_the_jwt_cookie_guard_driver(): void
    {
        Config::set('auth.guards.testApi', [
            'driver' => 'jwtCookie',
            'provider' => 'users',
            'cookie' => 'testToken',
            'cookiePath' => '/test',
        ]);

        Config::set('auth.providers.users.driver', 'eloquent');
        Config::set('auth.providers.users.model', \Illuminate\Foundation\Auth\User::class);

        $guard = Auth::guard('testApi');

        $this->assertInstanceOf(JwtCookieGuard::class, $guard);
    }
}

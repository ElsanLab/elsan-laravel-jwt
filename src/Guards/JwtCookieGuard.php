<?php

namespace Elsan\JwtCookieGuard\Guards;

use Elsan\JwtCookieGuard\Contracts\RevisableJWTSubject;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use PHPOpenSourceSaver\JWTAuth\Http\Parser\Cookies;
use PHPOpenSourceSaver\JWTAuth\Http\Parser\Parser;
use PHPOpenSourceSaver\JWTAuth\JWT;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Symfony\Component\HttpFoundation\Cookie;

class JwtCookieGuard extends JWTGuard
{
    public function __construct(
        private readonly string $cookieName,
        private readonly string $cookiePath,
        private readonly TokenRevisionChecker $tokenRevisionChecker,
        JWT $jwt,
        UserProvider $provider,
        Request $request,
        Dispatcher $eventDispatcher,
    ) {
        parent::__construct($jwt, $provider, $request, $eventDispatcher);
    }

    public static function make(Application $app, array $config): self
    {
        $parser = new Parser($app['request'], [
            (new Cookies(false))->setKey($config['cookie']),
        ]);

        $app->refresh('request', $parser, 'setRequest');

        $guard = new self(
            $config['cookie'],
            $config['cookiePath'],
            $app->make(TokenRevisionChecker::class),
            new JWT($app['tymon.jwt.manager'], $parser),
            $app['auth']->createUserProvider($config['provider']),
            $app['request'],
            $app['events'],
        );

        $guard->setTTL($config['ttl'] ?? config('jwt.ttl'));

        $app->refresh('request', $guard, 'setRequest');

        return $guard;
    }

    public function login(JWTSubject $user): string
    {
        if ($user instanceof RevisableJWTSubject && ($column = $user->getTokenRevisionColumn()) !== null) {
            $this->claims(['rev' => $user->{$column}]);
        }

        return parent::login($user);
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $user = parent::user();

        if ($user === null) {
            return null;
        }

        if (! $this->tokenRevisionChecker->isValid($user, $this->payload())) {
            $this->user = null;

            return null;
        }

        return $user;
    }

    public function makeCookie(string $token, int $minutes = 0): Cookie
    {
        return cookie(
            $this->cookieName,
            $token,
            $minutes,
            $this->cookiePath,
            null,
            ! app()->environment('local'),
            true,
            false,
            config('session.same_site'),
        );
    }
}

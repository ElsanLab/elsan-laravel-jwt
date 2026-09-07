<?php

namespace Elsan\JwtCookieGuard\Guards;

use Elsan\JwtCookieGuard\Contracts\RevisableJWTSubject;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Payload;
use RuntimeException;

class TokenRevisionChecker
{
    public function isValid(Authenticatable $user, Payload $payload): bool
    {
        if (! $user instanceof RevisableJWTSubject) {
            return true;
        }

        $column = $user->getTokenRevisionColumn();

        if ($column === null) {
            return true;
        }

        if (! isset($user->{$column})) {
            throw new RuntimeException("Token revision column [{$column}] is not set on the user model.");
        }

        return $payload->get('rev') === $user->{$column};
    }
}

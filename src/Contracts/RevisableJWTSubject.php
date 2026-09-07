<?php

namespace Elsan\JwtCookieGuard\Contracts;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

interface RevisableJWTSubject extends JWTSubject
{
    /**
     * The name of the column holding the token revision counter, or null if this model does not support token revision checks.
     */
    public function getTokenRevisionColumn(): ?string;
}

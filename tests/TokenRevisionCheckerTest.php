<?php

namespace Elsan\JwtCookieGuard\Tests;

use Elsan\JwtCookieGuard\Contracts\RevisableJWTSubject;
use Elsan\JwtCookieGuard\Guards\TokenRevisionChecker;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Payload;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class TokenRevisionCheckerTest extends TestCase
{
    #[Test]
    public function it_is_valid_when_the_user_is_not_revisable(): void
    {
        $user = $this->createMock(Authenticatable::class);
        $payload = $this->createMock(Payload::class);

        $this->assertTrue((new TokenRevisionChecker())->isValid($user, $payload));
    }

    #[Test]
    public function it_is_valid_when_the_revisable_user_has_no_revision_column(): void
    {
        $user = new class implements Authenticatable, RevisableJWTSubject
        {
            use \Illuminate\Auth\Authenticatable;

            public function getJWTIdentifier(): mixed
            {
                return 1;
            }

            public function getJWTCustomClaims(): array
            {
                return [];
            }

            public function getTokenRevisionColumn(): ?string
            {
                return null;
            }
        };

        $payload = $this->createMock(Payload::class);

        $this->assertTrue((new TokenRevisionChecker())->isValid($user, $payload));
    }

    #[Test]
    public function it_matches_the_payload_revision_against_the_user_column(): void
    {
        $user = new class implements Authenticatable, RevisableJWTSubject
        {
            use \Illuminate\Auth\Authenticatable;

            public int $tokenRevision = 3;

            public function getJWTIdentifier(): mixed
            {
                return 1;
            }

            public function getJWTCustomClaims(): array
            {
                return [];
            }

            public function getTokenRevisionColumn(): ?string
            {
                return 'tokenRevision';
            }
        };

        $matchingPayload = $this->createMock(Payload::class);
        $matchingPayload->method('get')->with('rev')->willReturn(3);

        $staleTokenPayload = $this->createMock(Payload::class);
        $staleTokenPayload->method('get')->with('rev')->willReturn(2);

        $checker = new TokenRevisionChecker();

        $this->assertTrue($checker->isValid($user, $matchingPayload));
        $this->assertFalse($checker->isValid($user, $staleTokenPayload));
    }

    #[Test]
    public function it_throws_when_the_revision_column_is_not_set_on_the_user(): void
    {
        $user = new class implements Authenticatable, RevisableJWTSubject
        {
            use \Illuminate\Auth\Authenticatable;

            public function getJWTIdentifier(): mixed
            {
                return 1;
            }

            public function getJWTCustomClaims(): array
            {
                return [];
            }

            public function getTokenRevisionColumn(): ?string
            {
                return 'tokenRevision';
            }
        };

        $payload = $this->createMock(Payload::class);

        $this->expectException(RuntimeException::class);

        (new TokenRevisionChecker())->isValid($user, $payload);
    }
}

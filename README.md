# elsan/laravel-jwt

Cookie-based JWT guard for Laravel, built on top of [php-open-source-saver/jwt-auth](https://github.com/PHP-Open-Source-Saver/jwt-auth), with per-user token revision support (invalidate all previously issued tokens for a user, e.g. on password change, without needing a blacklist).

## Installation

```bash
composer require elsan/laravel-jwt
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

The `jwtCookie` auth driver is auto-registered by this package's service provider — no manual `Auth::extend()` call needed.

## Usage

### 1. Configure a guard

```php
// config/auth.php
'guards' => [
    'adminApi' => [
        'driver' => 'jwtCookie',
        'provider' => 'adminProvider',
        'cookie' => 'adminUserToken',
        'cookiePath' => '/admin',
    ],
],
```

- `cookie` — the name of the cookie the token is stored in and read from. Each guard should use its own name; sharing one across guards means logging in with one guard overwrites the other's session cookie in the browser.
- `cookiePath` — the `Path` attribute set on the cookie (see [RFC 6265](https://datatracker.ietf.org/doc/html/rfc6265)). The browser only sends the cookie back on requests whose URL starts with this path, so it **must match the actual route prefix** these guard's routes are served under (e.g. `/admin/...`) — not just a name that looks related. A mismatch here doesn't error: the cookie is simply never sent back, and every authenticated request after login fails as if unauthenticated.

### 2. (Optional) Opt a model into token revision

Implement `RevisableJWTSubject` (extends the base `JWTSubject` contract) instead of `JWTSubject` directly, and add the `RevokesTokenOnPasswordChange` trait to auto-increment the revision whenever the password changes:

```php
use Elsan\JwtCookieGuard\Concerns\RevokesTokenOnPasswordChange;
use Elsan\JwtCookieGuard\Contracts\RevisableJWTSubject;

class User extends Authenticatable implements RevisableJWTSubject
{
    use RevokesTokenOnPasswordChange;

    public function getTokenRevisionColumn(): ?string
    {
        return 'tokenRevision';
    }

    // getJWTIdentifier() / getJWTCustomClaims() as usual — the `rev` claim
    // is injected automatically by the guard at login time, no need to add
    // it to getJWTCustomClaims() yourself.
}
```

A model that doesn't implement `RevisableJWTSubject` (or returns `null` from `getTokenRevisionColumn()`) is unaffected — the guard behaves like a plain cookie-based JWT guard for it.

### 3. Build the response cookie

```php
$token = Auth::guard('adminApi')->login($user);

return response()
    ->json(['user' => $user])
    ->withCookie(Auth::guard('adminApi')->makeCookie($token, $minutes));
```

## Testing

```bash
composer install
vendor/bin/phpunit
```

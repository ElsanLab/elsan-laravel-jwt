<?php

namespace Elsan\JwtCookieGuard\Concerns;

use Elsan\JwtCookieGuard\Contracts\RevisableJWTSubject;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \Illuminate\Contracts\Auth\Authenticatable
 */
trait RevokesTokenOnPasswordChange
{
    public static function bootRevokesTokenOnPasswordChange(): void
    {
        static::saving(function (self $model): void {
            if (! $model instanceof RevisableJWTSubject || ! $model->isDirty($model->getAuthPasswordName())) {
                return;
            }

            $column = $model->getTokenRevisionColumn();

            if ($column === null) {
                return;
            }

            $model->{$column} = ($model->{$column} ?? 1) + 1;
        });
    }
}

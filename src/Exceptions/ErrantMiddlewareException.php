<?php
declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

final class ErrantMiddlewareException extends TenantedException
{
    public static function shouldNotRun(string $middleware, ?string $route): self
    {
        return new self(
            sprintf(
                'The middleware [%s] serves as a marker, and should not be left to run on route [%s]',
                $middleware,
                $route ?? 'unknown'
            )
        );
    }
}
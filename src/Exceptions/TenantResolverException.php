<?php
declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

final class TenantResolverException extends TenantedException
{
    public static function noRoute(string $name): self
    {
        return new self(
            sprintf(
                'The current request has no route, and the tenant resolver [%s] requires one',
                $name
            )
        );
    }

    public static function noIdentifier(string $missingName, string $identifierName, string $name): self
    {
        return new self(
            sprintf(
                'The current request is missing its %s [%s], and the tenant resolver [%s] requires one',
                $missingName,
                $identifierName,
                $name
            )
        );
    }

    public static function missingIdentifier(): self
    {
        return new self('The current request has no tenant identifier');
    }

    public static function missingConfig(string $name): self
    {
        return new self(
            sprintf(
                'No config found for tenant resolver [%s]',
                $name
            )
        );
    }

    public static function missingDriver(string $name): self
    {
        return new self(
            sprintf(
                'No driver found for tenant resolver [%s]',
                $name
            )
        );
    }

    public static function unknown(string $name): self
    {
        return new self(
            sprintf(
                'No tenant resolver found [%s]',
                $name
            )
        );
    }

    public static function missingConfigValue(string $value, string $name): self
    {
        return new self(
            sprintf(
                'Configuration value [%s] not found for tenant resolver [%s]',
                $value,
                $name
            )
        );
    }
}
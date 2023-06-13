<?php
declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

final class TenancyException extends TenantedException
{
    public static function missingConfig(string $name): self
    {
        return new self(
            sprintf(
                'No config found for tenancy [%s]',
                $name
            )
        );
    }

    public static function missingDriver(string $name): self
    {
        return new self(
            sprintf(
                'No driver found for tenancy [%s]',
                $name
            )
        );
    }

    public static function unknown(string $name, ?\Throwable $previous = null): self
    {
        return new self(
            message : sprintf(
                          'No tenancy found [%s]',
                          $name
                      ),
            previous: $previous
        );
    }

    public static function missingValue(string $value, string $name): self
    {
        return new self(
            sprintf(
                'Configuration value [%s] not found for tenancy [%s]',
                $value,
                $name
            )
        );
    }

    public static function noTenant(string $name): self
    {
        return new self(
            sprintf(
                'No current tenant for tenancy [%s]',
                $name
            )
        );
    }
}
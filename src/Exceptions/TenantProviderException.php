<?php
declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

final class TenantProviderException extends TenantedException
{
    public static function missingConfig(string $name): self
    {
        return new self(
            sprintf(
                'No config found for tenant provider [%s]',
                $name
            )
        );
    }

    public static function missingDriver(string $name): self
    {
        return new self(
            sprintf(
                'No driver found for tenant provider [%s]',
                $name
            )
        );
    }

    public static function unknown(string $name): self
    {
        return new self(
            sprintf(
                'No tenant provider found [%s]',
                $name
            )
        );
    }

    public static function missingValue(string $value, string $name): self
    {
        return new self(
            sprintf(
                'Configuration value [%s] not found for tenant provider [%s]',
                $value,
                $name
            )
        );
    }
}
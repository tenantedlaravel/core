<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Exceptions\TenancyException;
use function Tenanted\Core\tenanted;

/**
 *
 */
final class TenantedHelper
{
    /**
     * @param string|null $resolver
     * @param string|null $tenancy
     *
     * @return string
     */
    public static function middleware(?string $resolver = null, ?string $tenancy = null): string
    {
        return 'tenanted:' . $tenancy . ',' . $resolver;
    }

    /**
     * @param string|null $resolver
     * @param string|null $tenancy
     * @param string|null $value
     *
     * @return string
     */
    public static function parameter(?string $resolver = null, ?string $tenancy = null, ?string $value = null): string
    {
        return '{' . self::parameterName($resolver, $tenancy) . ($value ? ':' . $value : '') . '}';
    }

    /**
     * @param string|null $resolver
     * @param string|null $tenancy
     *
     * @return string
     */
    public static function parameterName(?string $resolver = null, ?string $tenancy = null): string
    {
        return ($tenancy ?? tenanted()->getDefaultTenancyName()) . '_' . ($resolver ?? tenanted()->getDefaultResolverName());
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string
     * @throws \Tenanted\Core\Exceptions\TenancyException
     */
    public static function tenantRelatedKeyName(Tenancy $tenancy): string
    {
        if (! $tenancy->check()) {
            throw TenancyException::noTenant($tenancy->name());
        }

        return $tenancy->name() . '_' . $tenancy->tenant()->getTenantKeyName();
    }

    /**
     * @param string|object $class
     * @param string        ...$trait
     *
     * @return bool
     */
    public static function classUses(string|object $class, string...$trait): bool
    {
        return in_array($trait, class_uses_recursive($class), true);
    }
}
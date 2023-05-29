<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Tenanted\Core\Contracts\Tenancy;
use function Tenanted\Core\tenanted;

final class TenantedHelper
{
    public static function middleware(?string $resolver = null, ?string $tenancy = null): string
    {
        return 'tenanted:' . $tenancy . ',' . $resolver;
    }

    public static function parameter(?string $resolver = null, ?string $tenancy = null, ?string $value = null): string
    {
        return '{' . self::parameterName($resolver, $tenancy) . ($value ? ':' . $value : '') . '}';
    }

    public static function parameterName(?string $resolver = null, ?string $tenancy = null): string
    {
        return ($tenancy ?? tenanted()->getDefaultTenancyName()) . '_' . ($resolver ?? tenanted()->getDefaultResolverName());
    }

    public static function tenantRelatedKeyName(Tenancy $tenancy): string
    {
        return $tenancy->name() . '_' . $tenancy->tenant()->getTenantKeyName();
    }

    public static function classUses(string|object $class, string...$trait): bool
    {
        return in_array($trait, class_uses_recursive($class) ?? [], true);
    }
}
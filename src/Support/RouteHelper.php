<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use function Tenanted\Core\tenanted;

final class RouteHelper
{

    public static function middleware(?string $resolver = null, ?string $tenancy = null): string
    {
        return 'tenanted:' . $resolver . ',' . $tenancy;
    }

    public static function parameter(?string $resolver = null, ?string $tenancy = null, ?string $value = null): string
    {
        return '{' . self::parameterName($resolver, $tenancy) . ($value ? ':' . $value : '') . '}';
    }

    public static function parameterName(?string $resolver = null, ?string $tenancy = null): string
    {
        return ($tenancy ?? tenanted()->getDefaultTenancyName()) . '_' . ($resolver ?? tenanted()->getDefaultResolverName());
    }
}
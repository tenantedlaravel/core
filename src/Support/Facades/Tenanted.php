<?php
declare(strict_types=1);

namespace Tenanted\Core\Support\Facades;

use Closure;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Facades\Facade;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Contracts\TenantResolver;
use Tenanted\Core\TenantedManager;

/**
 * Tenanted Facade
 *
 * @method static TenantedManager registerProvider(string $name, Closure $creator)
 * @method static TenantedManager registerSource(string $name, Closure $resolver)
 * @method static TenantedManager registerResolver(string $name, Closure $creator)
 * @method static TenantedManager registerTenancy(string $name, Closure $creator)
 * @method static string getDefaultProviderName()
 * @method static string getDefaultResolverName()
 * @method static string getDefaultTenancyName()
 * @method static TenantProvider provider(?string $name = null)
 * @method static TenantResolver resolver(?string $name = null)
 * @method static Tenancy tenancy(?string $name = null)
 * @method static Tenancy[] tenancyStack()
 * @method static TenantedManager stackTenancy(Tenancy $tenancy)
 * @method static RouteRegistrar routes(?string $resolver = null, ?string $tenancy = null, ?string $value = null)
 *
 * @see \Tenanted\Core\TenantedManager
 */
final class Tenanted extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TenantedManager::class;
    }
}
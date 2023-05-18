<?php
declare(strict_types=1);

namespace Tenanted\Core\Support\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Support\TenantedManager;

/**
 * Tenanted Facade
 *
 * @method static TenantProvider provider(?string $name = null)
 * @method static TenantedManager registerProvider(string $name, Closure $creator)
 * @method static TenantedManager registerSource(string $name, Closure $resolver)
 *
 * @see \Tenanted\Core\Support\TenantedManager
 */
final class Tenanted extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TenantedManager::class;
    }
}
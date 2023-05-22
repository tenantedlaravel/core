<?php

namespace Tenanted\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tenanted\Core\Contracts\ActsAsMiddleware;
use Tenanted\Core\Exceptions\ErrantMiddlewareException;
use Tenanted\Core\TenantedManager;

/**
 * Tenanted Routes
 *
 * This middleware marks a route and is parsed by the handler. It doesn't
 * function as a piece of middleware.
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
class TenantedRoutes
{
    /**
     * @var \Tenanted\Core\TenantedManager
     */
    private TenantedManager $manager;

    public function __construct(TenantedManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $tenancy
     * @param string|null              $resolver
     *
     * @return mixed
     *
     * @throws \Tenanted\Core\Exceptions\ErrantMiddlewareException
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function handle(Request $request, Closure $next, ?string $tenancy = null, ?string $resolver = null): mixed
    {
        $resolverInstance = $this->manager->resolver($resolver);

        if ($resolverInstance instanceof ActsAsMiddleware) {
            return $resolverInstance->handle($request, $next, $this->manager->tenancy($tenancy));
        }

        throw ErrantMiddlewareException::shouldNotRun(self::class, $request->route()?->getName());
    }
}

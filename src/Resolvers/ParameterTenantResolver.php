<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Tenanted\Core\Contracts\ActsAsMiddleware;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Support\TenantedHelper;

abstract class ParameterTenantResolver extends BaseTenantResolver implements ActsAsMiddleware
{
    abstract protected function fallbackResolution(Request $request): ?string;

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return bool
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function resolve(Request $request, Tenancy $tenancy): bool
    {
        $route     = $request->route();
        $parameter = TenantedHelper::parameterName($this->name(), $tenancy->name());
        $binding   = null;

        if ($route === null || ! $route->hasParameter($parameter)) {
            $identifier = $this->fallbackResolution($request);
        } else {
            $identifier = $route->parameter($parameter);
        }

        if (! is_string($identifier)) {
            throw TenantResolverException::missingIdentifier();
        }

        if ($route !== null) {
            $route->forgetParameter($identifier);
            $binding = $route->bindingFieldFor($parameter);
        }

        return $this->handleIdentifier($tenancy, $identifier, $binding);
    }

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Closure                         $next
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return mixed
     */
    public function asMiddleware(Request $request, Closure $next, Tenancy $tenancy): mixed
    {
        if ($tenancy->resolver() === $this && $tenancy->check()) {
            app(UrlGenerator::class)->defaults([TenantedHelper::parameterName($this->name(), $tenancy->name()) => $tenancy->identifiedUsing()]);
        }

        return $next($request);
    }
}
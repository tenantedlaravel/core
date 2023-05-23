<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Contracts\ActsAsMiddleware;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Support\RouteHelper;

class HeaderTenantResolver extends BaseTenantResolver implements ActsAsMiddleware
{
    private ?string $header;

    public function __construct(string $name, ?string $cookie)
    {
        parent::__construct($name);

        $this->header = $cookie;
    }

    public function resolve(Request $request, Tenancy $tenancy): bool
    {
        $header = $request->header($this->header);

        if (! $header) {
            throw TenantResolverException::noIdentifier('header', $this->header, $this->name());
        }

        return $this->handleIdentifier($tenancy, $header);
    }

    public function routes(?string $tenancy = null, ?string $value = null): RouteRegistrar
    {
        return app(Router::class)->middleware(RouteHelper::middleware($this->name(), $tenancy));
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
        $response = $next($request);

        if ($response instanceof Response && $tenancy->resolver() === $this && $tenancy->check()) {
            return $response->header(
                $this->header,
                $tenancy->identifiedUsing()
            );
        }

        return $response;
    }
}
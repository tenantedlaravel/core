<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Contracts\ActsAsMiddleware;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Support\RouteHelper;

class CookieTenantResolver extends BaseTenantResolver implements ActsAsMiddleware
{
    /**
     * @var \Illuminate\Cookie\CookieJar
     */
    private CookieJar $cookieJar;

    private ?string $cookie;

    public function __construct(string $name, CookieJar $cookieJar, ?string $cookie = null)
    {
        parent::__construct($name);

        $this->cookieJar = $cookieJar;
        $this->cookie    = $cookie;
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string
     */
    protected function getCookieName(Tenancy $tenancy): string
    {
        return $this->cookie ?? RouteHelper::parameterName($this->name(), $tenancy->name());
    }

    public function resolve(Request $request, Tenancy $tenancy): bool
    {
        $cookieName = $this->getCookieName($tenancy);

        if (! $request->hasCookie($cookieName)) {
            throw TenantResolverException::noIdentifier('cookie', $cookieName, $this->name());
        }

        return $this->handleIdentifier($tenancy, $request->cookie($cookieName));
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
        $cookie   = $this->getCookieName($tenancy);

        if ($response instanceof Response && $tenancy->resolver() === $this && $tenancy->check() && ! $this->cookieJar->hasQueued($cookie)) {
            return $response->withCookie($this->cookieJar->make(
                $cookie,
                $tenancy->identifiedUsing()
            ));
        }

        return $response;
    }
}
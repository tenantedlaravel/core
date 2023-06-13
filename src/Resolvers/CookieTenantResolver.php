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
use Tenanted\Core\Exceptions\TenancyException;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Support\TenantedHelper;

/**
 *
 */
class CookieTenantResolver extends BaseTenantResolver implements ActsAsMiddleware
{
    /**
     * @var \Illuminate\Cookie\CookieJar
     */
    private CookieJar $cookieJar;

    /**
     * @var string|null
     */
    private ?string $cookie;

    /**
     * @param string                       $name
     * @param \Illuminate\Cookie\CookieJar $cookieJar
     * @param string|null                  $cookie
     */
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
        return $this->cookie ?? TenantedHelper::parameterName($this->name(), $tenancy->name());
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string                   $cookieName
     *
     * @return string|null
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     */
    private function getIdentifierFromCookie(Request $request, string $cookieName): ?string
    {
        $identifier = $request->cookie($cookieName);

        if (is_array($identifier)) {
            throw new TenancyException('Multiple cookies present for tenancy identifier');
        }

        return $identifier;
    }

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return bool
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function resolve(Request $request, Tenancy $tenancy): bool
    {
        $cookieName = $this->getCookieName($tenancy);
        $identifier = $this->getIdentifierFromCookie($request, $cookieName);

        if ($identifier === null) {
            throw TenantResolverException::noIdentifier('cookie', $cookieName, $this->name());
        }

        return $this->handleIdentifier($tenancy, $identifier);
    }

    /**
     * @param string|null $tenancy
     * @param string|null $value
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(?string $tenancy = null, ?string $value = null): RouteRegistrar
    {
        return app(Router::class)->middleware(TenantedHelper::middleware($this->name(), $tenancy));
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
                $tenancy->identifiedUsing() // @phpstan-ignore-line
            ));
        }

        return $response;
    }
}
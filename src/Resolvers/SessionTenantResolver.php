<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Contracts\ActsAsMiddleware;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Support\TenantedHelper;

class SessionTenantResolver extends BaseTenantResolver implements ActsAsMiddleware
{
    /**
     * @var \Illuminate\Contracts\Session\Session
     */
    private Session $session;

    private ?string $sessionName;

    public function __construct(string $name, Session $session, ?string $sessionName = null)
    {
        parent::__construct($name);

        $this->session     = $session;
        $this->sessionname = $sessionName;
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string
     */
    protected function getSessionName(Tenancy $tenancy): string
    {
        return $this->sessionName ?? TenantedHelper::parameterName($this->name(), $tenancy->name());
    }

    public function resolve(Request $request, Tenancy $tenancy): bool
    {
        $sessionName = $this->getSessionName($tenancy);

        if (! $this->session->has($sessionName)) {
            throw TenantResolverException::noIdentifier('session', $sessionName, $this->name());
        }

        return $this->handleIdentifier($tenancy, $this->session->get($sessionName));
    }

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
        $session = $this->getSessionName($tenancy);

        if ($tenancy->check() && ! $this->session->has($session)) {
            $this->session->put($session, $tenancy->tenant());
        }

        return $next($request);
    }
}
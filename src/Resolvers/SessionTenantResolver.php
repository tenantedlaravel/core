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

/**
 *
 */
class SessionTenantResolver extends BaseTenantResolver implements ActsAsMiddleware
{
    /**
     * @var \Illuminate\Contracts\Session\Session
     */
    private Session $session;

    /**
     * @var string|null
     */
    private ?string $sessionName;

    /**
     * @param string                                $name
     * @param \Illuminate\Contracts\Session\Session $session
     * @param string|null                           $sessionName
     */
    public function __construct(string $name, Session $session, ?string $sessionName = null)
    {
        parent::__construct($name);

        $this->session     = $session;
        $this->sessionName = $sessionName;
    }

    /**
     * @param string $sessionName
     *
     * @return string|null
     */
    private function getIdentifierFromSession(string $sessionName): ?string
    {
        return $this->session->get($sessionName);
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

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return bool
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function resolve(Request $request, Tenancy $tenancy): bool
    {
        $sessionName = $this->getSessionName($tenancy);
        $identifier  = $this->getIdentifierFromSession($sessionName);

        if ($identifier === null) {
            throw TenantResolverException::noIdentifier('session', $sessionName, $this->name());
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
        $session = $this->getSessionName($tenancy);

        if ($tenancy->check() && ! $this->session->has($session)) {
            $this->session->put($session, $tenancy->tenant());
        }

        return $next($request);
    }
}
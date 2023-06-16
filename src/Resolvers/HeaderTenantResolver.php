<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\ActsAsMiddleware;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Support\TenantedHelper;

/**
 *
 */
class HeaderTenantResolver extends BaseTenantResolver implements ActsAsMiddleware
{
    /**
     * @var string|null
     */
    private ?string $header = null;

    /**
     * @param string      $name
     * @param string|null $header
     */
    public function __construct(string $name, ?string $header)
    {
        parent::__construct($name);

        $this->header = $header;
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return string
     */
    protected function getHeaderName(Tenancy $tenancy): string
    {
        return $this->header ?? Str::ucfirst($tenancy->name());
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
        $header = $request->header($this->getHeaderName($tenancy));

        if (! $header || is_array($header)) {
            throw TenantResolverException::noIdentifier('header', $this->getHeaderName($tenancy), $this->name());
        }

        return $this->handleIdentifier($tenancy, $header);
    }

    /**
     * @param string|null $tenancy
     * @param string|null $value
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(?string $tenancy = null, ?string $value = null): RouteRegistrar
    {
        return app()->make(Router::class)->middleware(TenantedHelper::middleware($this->name(), $tenancy));
    }

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Closure                         $next
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return mixed
     *
     * @psalm-suppress MixedReturnStatement
     */
    public function asMiddleware(Request $request, Closure $next, Tenancy $tenancy): mixed
    {
        $response = $next($request);

        if ($response instanceof Response && $tenancy->resolver() === $this && $tenancy->check()) {
            return $response->header(
                $this->getHeaderName($tenancy),
                (string)$tenancy->identifiedUsing() // @phpstan-ignore-line
            );
        }

        return $response;
    }
}
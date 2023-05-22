<?php
declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Foundation\Application;
use Illuminate\Routing\RouteRegistrar;

/**
 *
 */
final class TenantedManager
{
    use Concerns\ManagesTenantProviders,
        Concerns\ManagesTenantResolvers,
        Concerns\ManagesTenancies;

    /**
     * @var \Illuminate\Foundation\Application
     */
    private Application $app;

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    public function getDefaultProviderName(): string
    {
        return $this->app['config']['tenanted.defaults.provider'];
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    protected function getProviderConfig(string $name): ?array
    {
        return $this->app['config']['tenanted.providers.' . $name] ?? null;
    }

    /**
     * @return string
     */
    public function getDefaultTenancyName(): string
    {
        return $this->app['config']['tenanted.defaults.tenancy'];
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    protected function getTenancyConfig(string $name): ?array
    {
        return $this->app['config']['tenancies.' . $name] ?? null;
    }

    /**
     * @return string
     */
    public function getDefaultResolverName(): string
    {
        return $this->app['config']['tenanted.defaults.resolver'];
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    protected function getResolverConfig(string $name): ?array
    {
        return $this->app['config']['tenanted.resolvers.' . $name] ?? null;
    }

    /**
     * @param string|null $resolver
     * @param string|null $tenancy
     * @param string|null $value
     *
     * @return \Illuminate\Routing\RouteRegistrar
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function routes(?string $resolver = null, ?string $tenancy = null, ?string $value = null): RouteRegistrar
    {
        return $this->resolver($resolver)->routes($tenancy, $value);
    }
}
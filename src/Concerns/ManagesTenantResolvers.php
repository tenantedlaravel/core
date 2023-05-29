<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\TenantResolver;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Resolvers\CookieTenantResolver;
use Tenanted\Core\Resolvers\HeaderTenantResolver;
use Tenanted\Core\Resolvers\PathTenantResolver;
use Tenanted\Core\Resolvers\SubdomainTenantResolver;

trait ManagesTenantResolvers
{
    /**
     * @var array<string, \Tenanted\Core\Contracts\TenantResolver>
     */
    protected array $resolvers = [];

    /**
     * @var array<string, \Closure(array, string): \Tenanted\Core\Contracts\TenantResolver>
     */
    protected array $resolverCreators = [];

    /**
     * @var list<\Tenanted\Core\Contracts\TenantResolver>
     */
    protected array $resolverStack = [];

    /**
     * Get the name of the default resolver
     *
     * @return string
     */
    abstract public function getDefaultResolverName(): string;

    /**
     * Get the resolver config by name
     *
     * @param string $name
     *
     * @return array|null
     */
    abstract protected function getResolverConfig(string $name): ?array;

    /**
     * Create a new tenant resolver
     *
     * @param string $name
     *
     * @return \Tenanted\Core\Contracts\TenantResolver
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    protected function makeTenantResolver(string $name): TenantResolver
    {
        $config = $this->getResolverConfig($name);

        if ($config === null) {
            throw TenantResolverException::missingConfig($name);
        }

        $driver = $config['driver'] ?? null;

        if ($driver === null) {
            throw TenantResolverException::missingDriver($name);
        }

        if (isset($this->resolverCreators[$driver])) {
            return call_user_func($this->resolverCreators[$driver], $config, $name);
        }

        $method = 'create' . Str::studly($driver) . 'Resolver';

        if (method_exists($this, $method)) {
            return $this->$method($config, $name);
        }

        throw TenantResolverException::unknown($name);
    }

    /**
     * Get a resolver
     *
     * @param string|null $name
     *
     * @return \Tenanted\Core\Contracts\TenantResolver
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function resolver(?string $name = null): TenantResolver
    {
        $name ??= $this->getDefaultResolverName();

        if (! isset($this->resolvers[$name])) {
            $this->resolvers[$name] = $this->makeTenantResolver($name);
        }

        return $this->resolvers[$name];
    }

    /**
     * Register a custom resolver creator
     *
     * @param string                                                           $name
     * @param callable(array, string): \Tenanted\Core\Contracts\TenantResolver $creator
     *
     * @return static
     */
    public function registerResolver(string $name, callable $creator): static
    {
        $this->resolverCreators[$name] = $creator;

        return $this;
    }

    /**
     * @param array  $config
     * @param string $name
     *
     * @return \Tenanted\Core\Resolvers\SubdomainTenantResolver
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    protected function createSubdomainResolver(array $config, string $name): SubdomainTenantResolver
    {
        if (! isset($config['domain'])) {
            throw TenantResolverException::missingConfigValue('domain', $name);
        }

        return new SubdomainTenantResolver($name, $config['domain']);
    }

    protected function createDomainResolver(array $config, string $name)
    {

    }

    protected function createPathResolver(array $config, string $name): PathTenantResolver
    {
        return new PathTenantResolver($name, $config['segment'] ?? 0);
    }

    /**
     * @param array  $config
     * @param string $name
     *
     * @return \Tenanted\Core\Resolvers\HeaderTenantResolver
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    protected function createHeaderResolver(array $config, string $name): HeaderTenantResolver
    {
        if (! isset($config['header'])) {
            throw TenantResolverException::missingConfigValue('header', $name);
        }

        return new HeaderTenantResolver($name, $config['header']);
    }

    protected function createSessionResolver(array $config, string $name)
    {

    }

    /**
     * @param array  $config
     * @param string $name
     *
     * @return \Tenanted\Core\Resolvers\CookieTenantResolver
     */
    protected function createCookieResolver(array $config, string $name): CookieTenantResolver
    {
        return new CookieTenantResolver($name, $this->app->make(CookieJar::class), $config['cookie'] ?? null);
    }

    /**
     * Stack a resolver
     *
     * @param \Tenanted\Core\Contracts\TenantResolver $resolver
     *
     * @return static
     */
    public function stackResolver(TenantResolver $resolver): static
    {
        $this->resolverStack[] = $resolver;

        return $this;
    }

    /**
     * Get the current resolver stack
     *
     * @return list<\Tenanted\Core\Contracts\TenantResolver>
     */
    public function resolverStack(): array
    {
        return $this->resolverStack;
    }
}
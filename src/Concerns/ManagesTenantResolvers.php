<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Illuminate\Contracts\Session\Session;
use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\TenantResolver;
use Tenanted\Core\Exceptions\TenantResolverException;
use Tenanted\Core\Resolvers\CookieTenantResolver;
use Tenanted\Core\Resolvers\HeaderTenantResolver;
use Tenanted\Core\Resolvers\PathTenantResolver;
use Tenanted\Core\Resolvers\SessionTenantResolver;
use Tenanted\Core\Resolvers\SubdomainTenantResolver;

/**
 */
trait ManagesTenantResolvers
{
    /**
     * @var array<string, \Tenanted\Core\Contracts\TenantResolver>
     */
    protected array $resolvers = [];

    /**
     * @var array<string, callable(array<string, mixed>, string): \Tenanted\Core\Contracts\TenantResolver>
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
        /**
         * @var array{driver: string|null}|null $config
         */
        $config = $this->getResolverConfig($name);

        if ($config === null) {
            throw TenantResolverException::missingConfig($name);
        }

        $driver = $config['driver'] ?? null;

        if ($driver === null) {
            throw TenantResolverException::missingDriver($name);
        }

        $creator = $this->resolverCreators[$driver] ?? null;

        if ($creator !== null) {
            return $creator($config, $name);
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
     * @param string                                                                          $name
     * @param callable(array<string, mixed>, string): \Tenanted\Core\Contracts\TenantResolver $creator
     *
     * @return static
     */
    public function registerResolver(string $name, callable $creator): static
    {
        $this->resolverCreators[$name] = $creator;

        return $this;
    }

    /**
     * @param array<string, mixed> $config
     * @param string               $name
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

        /**
         * @var array{domain:string} $config
         */

        return new SubdomainTenantResolver($name, $config['domain']);
    }

    /**
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Resolvers\PathTenantResolver
     */
    protected function createPathResolver(array $config, string $name): PathTenantResolver
    {
        /**
         * @var array{segment:int|null} $config
         */

        return new PathTenantResolver($name, $config['segment'] ?? 0);
    }

    /**
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Resolvers\HeaderTenantResolver
     */
    protected function createHeaderResolver(array $config, string $name): HeaderTenantResolver
    {
        /**
         * @var array{header:string|null} $config
         */

        return new HeaderTenantResolver($name, $config['header'] ?? null);
    }

    /**
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Resolvers\SessionTenantResolver
     */
    protected function createSessionResolver(array $config, string $name): SessionTenantResolver
    {
        /**
         * @var array{session:string|null} $config
         */

        return new SessionTenantResolver($name, $this->app->make(Session::class), $config['session'] ?? null);
    }

    /**
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Resolvers\CookieTenantResolver
     */
    protected function createCookieResolver(array $config, string $name): CookieTenantResolver
    {
        /**
         * @var array{cookie:string|null} $config
         */

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
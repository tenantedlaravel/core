<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Exceptions\TenantProviderException;
use Tenanted\Core\Providers\ArrayTenantProvider;
use Tenanted\Core\Providers\DatabaseTenantProvider;
use Tenanted\Core\Providers\EloquentTenantProvider;

/**
 *
 */
trait ManagesTenantProviders
{
    /**
     * @var array<string, \Tenanted\Core\Contracts\TenantProvider>
     */
    protected array $providers = [];

    /**
     * @var array<string, callable(array<string, mixed>, string): \Tenanted\Core\Contracts\TenantProvider>
     */
    protected array $providerCreators = [];

    /**
     * @var array<string, callable(array<string, mixed>): array<int, array<string, mixed>>>
     */
    protected array $sourceResolvers = [];

    /**
     * @var list<\Tenanted\Core\Contracts\TenantProvider>
     */
    protected array $providerStack = [];

    /**
     * Get the name of the default tenant provider
     *
     * @return string
     */
    abstract public function getDefaultProviderName(): string;

    /**
     * Get the tenant provider config by name
     *
     * @param string $name
     *
     * @return array<string, mixed>|null
     */
    abstract protected function getProviderConfig(string $name): ?array;

    /**
     * Create a new tenant provider
     *
     * @param string $name
     *
     * @return \Tenanted\Core\Contracts\TenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     *
     * @psalm-suppress MixedInferredReturnType
     */
    protected function makeProvider(string $name): TenantProvider
    {
        /**
         * @var array{driver: string|null}|null $config
         */
        $config = $this->getProviderConfig($name);

        if ($config === null) {
            throw TenantProviderException::missingConfig($name);
        }

        $driver = $config['driver'] ?? null;

        if ($driver === null) {
            throw TenantProviderException::missingDriver($name);
        }

        $creator = $this->providerCreators[$driver] ?? null;

        if ($creator !== null) {
            return $creator($config, $name);
        }

        $method = 'create' . Str::studly($driver) . 'Provider';

        if (method_exists($this, $method)) {
            return $this->$method($config, $name);
        }

        throw TenantProviderException::unknown($name);
    }

    /**
     * Get a tenant provider
     *
     * @param string|null $name
     *
     * @return \Tenanted\Core\Contracts\TenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function provider(?string $name = null): TenantProvider
    {
        $name ??= $this->getDefaultProviderName();

        if (! isset($this->providers[$name])) {
            $this->providers[$name] = $this->makeProvider($name);
        }

        return $this->providers[$name];
    }

    /**
     * Register a custom tenant provider creator
     *
     * @param string                                                                          $name
     * @param callable(array<string, mixed>, string): \Tenanted\Core\Contracts\TenantProvider $creator
     *
     * @return static
     */
    public function registerProvider(string $name, callable $creator): static
    {
        $this->providerCreators[$name] = $creator;

        return $this;
    }

    /**
     * Register a custom source resolver for the array tenant provider
     *
     * @param string                                                           $name
     * @param callable(array<string, mixed>): array<int, array<string, mixed>> $resolver
     *
     * @return static
     */
    public function registerSource(string $name, callable $resolver): static
    {
        $this->sourceResolvers[$name] = $resolver;

        return $this;
    }

    /**
     * Create a new eloquent tenant provider
     *
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Providers\EloquentTenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    protected function createEloquentProvider(array $config, string $name): EloquentTenantProvider
    {
        if (! isset($config['model'])) {
            throw TenantProviderException::missingValue('model', $name);
        }

        /**
         * @var array{model:class-string<\Illuminate\Database\Eloquent\Model&\Tenanted\Core\Contracts\Tenant>} $config
         */

        /** @psalm-suppress MixedArgument */
        return new EloquentTenantProvider($name, $config['model']);
    }

    /**
     * Create a new database tenant provider
     *
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Providers\DatabaseTenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    protected function createDatabaseProvider(array $config, string $name): DatabaseTenantProvider
    {
        if (! isset($config['table'])) {
            throw TenantProviderException::missingValue('table', $name);
        }

        /**
         * @var array{table:string, connection:string|null, identifier:string|null, key:string|null, entity:class-string<\Tenanted\Core\Contracts\Tenant>|null} $config
         */

        /** @psalm-suppress MixedArgument */
        return new DatabaseTenantProvider(
            $name,
            app(DatabaseManager::class)->connection($config['connection'] ?? null),
            $config['table'],
            $config['identifier'] ?? null,
            $config['key'] ?? null,
            $config['entity'] ?? null
        );
    }

    /**
     * Create a new array tenant provider
     *
     * @param array<string, mixed> $config
     * @param string               $name
     *
     * @return \Tenanted\Core\Providers\ArrayTenantProvider
     *
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     * @throws \JsonException
     */
    protected function createArrayProvider(array $config, string $name): ArrayTenantProvider
    {
        if (! isset($config['source'])) {
            throw TenantProviderException::missingValue('source', $name);
        }

        /**
         * @var array{source:array<string, mixed>} $config
         */

        if (! isset($config['source']['type'])) {
            throw TenantProviderException::missingValue('source.type', $name);
        }

        /** @psalm-suppress MixedArgument */
        $data = $this->getTenantsForSource($config['source']);

        /**
         * @var array{source:array{type:string}, identifier:string|null, key:string|null, entity:class-string<\Tenanted\Core\Contracts\Tenant>|null} $config
         */

        return new ArrayTenantProvider(
            $name,
            $data,
            $config['identifier'] ?? null,
            $config['key'] ?? null,
            $config['entity'] ?? null
        );
    }

    /**
     * Get a list of tenants from a given source
     *
     * @param array<string, mixed> $config
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws \JsonException
     *
     * @psalm-suppress MixedInferredReturnType
     */
    protected function getTenantsForSource(array $config): array
    {
        /**
         * @var array{type:string} $config
         */
        $type     = $config['type'];
        $resolver = $this->sourceResolvers[$type] ?? null;

        if ($resolver !== null) {
            return $resolver($config);
        }

        if ($type === 'php') {
            /**
             * @var array{type:string,data:array<int, array<string, mixed>>}|array{type:string,path:string} $config
             */

            if (isset($config['data'])) {
                return $config['data'];
            }

            if (isset($config['path'])) {
                /** @psalm-suppress UnresolvableInclude */
                return require $config['path'];
            }

            throw new \RuntimeException('No source for PHP');
        }

        if ($type === 'json') {
            /**
             * @var array{type:string,data:string}|array{type:string,path:string} $config
             */

            if (! isset($config['data']) && ! isset($config['path'])) {
                throw new \RuntimeException('No data or path for json');
            }

            // @phpstan-ignore-next-line
            return json_decode($config['data'] ?? file_get_contents($config['path']), true, 512, JSON_THROW_ON_ERROR);
        }

        /**
         * @var array{type:string,data:array<int, array<string, mixed>>|string|null,path:string|null} $config
         */

        throw new InvalidArgumentException('Unknown source type');
    }

    /**
     * Stack a tenant provider
     *
     * @param \Tenanted\Core\Contracts\TenantProvider $provider
     *
     * @return static
     */
    public function stackProvider(TenantProvider $provider): static
    {
        $this->providerStack[] = $provider;

        return $this;
    }

    /**
     * Get the current tenant provider stack
     *
     * @return list<\Tenanted\Core\Contracts\TenantProvider>
     */
    public function providerStack(): array
    {
        return $this->providerStack;
    }
}
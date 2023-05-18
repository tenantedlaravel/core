<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
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
     * @var array<string, \Closure(array, string): \Tenanted\Core\Contracts\TenantProvider>
     */
    protected array $providerCreators = [];

    /**
     * @var array<string, \Closure(array): array<int, array>
     */
    protected array $sourceResolvers = [];

    /**
     * Get the name of the default tenant provider
     *
     * @return string
     */
    abstract protected function getDefaultProviderName(): string;

    /**
     * Get the tenant provider config by name
     *
     * @param string $name
     *
     * @return array|null
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
     */
    protected function makeProvider(string $name): TenantProvider
    {
        $config = $this->getProviderConfig($name);

        if ($config === null) {
            throw TenantProviderException::missingConfig($name);
        }

        if (isset($this->providerCreators[$name])) {
            return call_user_func($this->providerCreators[$name], $config, $name);
        }

        $driver = $config['driver'] ?? null;

        if ($driver === null) {
            throw TenantProviderException::missingDriver($name);
        }

        if (isset($this->providerCreators[$driver])) {
            return call_user_func($this->providerCreators[$driver], $config, $name);
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
     * @param string                                                           $name
     * @param callable(array, string): \Tenanted\Core\Contracts\TenantProvider $creator
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
     * @param string   $name
     * @param callable $resolver
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
     * @param array  $config
     * @param string $name
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

        return new EloquentTenantProvider($name, $config['model']);
    }

    /**
     * Create a new database tenant provider
     *
     * @param array  $config
     * @param string $name
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
     * @param array  $config
     * @param string $name
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

        if (! isset($config['source']['type'])) {
            throw TenantProviderException::missingValue('source.type', $name);
        }

        $data = $this->getTenantsForSource($config['source']);

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
     * @param array $config
     *
     * @return array<int, array>
     *
     * @throws \JsonException
     */
    protected function getTenantsForSource(array $config): array
    {
        $type = $config['type'];

        if (isset($this->sourceResolvers[$type])) {
            return call_user_func($this->sourceResolvers[$type], $config);
        }

        return match ($type) {
            'php'  => $config['data'] ?? require $config['path'],
            'json' => json_decode($config['data'] ?? file_get_contents($config['path']), true, 512, JSON_THROW_ON_ERROR)
        };
    }
}
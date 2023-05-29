<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Illuminate\Config\Repository;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Exceptions\TenancyException;

trait ManagesTenancies
{
    /**
     * @var array<string, \Tenanted\Core\Contracts\Tenancy>
     */
    protected array $tenancies = [];

    /**
     * @var array<string, \Closure(array, string): \Tenanted\Core\Contracts\Tenancy>
     */
    protected array $tenancyCreators = [];

    /**
     * @var list<\Tenanted\Core\Contracts\Tenancy>
     */
    protected array $tenancyStack = [];

    protected ?Tenancy $current = null;

    /**
     * Get the name of the default tenancy
     *
     * @return string
     */
    abstract public function getDefaultTenancyName(): string;

    /**
     * Get the tenancy config by name
     *
     * @param string $name
     *
     * @return array|null
     */
    abstract protected function getTenancyConfig(string $name): ?array;

    /**
     * Create a new tenant provider
     *
     * @param string $name
     *
     * @return \Tenanted\Core\Contracts\Tenancy
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    protected function makeTenancy(string $name): Tenancy
    {
        $config = $this->getTenancyConfig($name);

        if ($config === null) {
            throw TenancyException::missingConfig($name);
        }

        if (isset($this->tenancyCreators[$name])) {
            return call_user_func($this->tenancyCreators[$name], $config, $name);
        }

        $provider   = $this->provider($config['provider'] ?? null);
        $configRepo = new Repository($config);

        $tenancy = new \Tenanted\Core\Tenancy(
            $name,
            $provider,
            $configRepo
        );

        if ($configRepo->has('resolver') && $configRepo->get('resolver') !== null) {
            $tenancy->use($this->resolver($configRepo['resolver']));
        }

        return $tenancy;
    }

    /**
     * Get a tenancy
     *
     * @param string|null $name
     *
     * @return \Tenanted\Core\Contracts\Tenancy
     *
     * @throws \Tenanted\Core\Exceptions\TenancyException
     * @throws \Tenanted\Core\Exceptions\TenantProviderException
     */
    public function tenancy(?string $name = null): Tenancy
    {
        $name ??= $this->getDefaultTenancyName();

        if (! isset($this->tenancies[$name])) {
            $this->tenancies[$name] = $this->makeTenancy($name);
        }

        return $this->tenancies[$name];
    }

    /**
     * Get the current tenancy
     *
     * @return \Tenanted\Core\Contracts\Tenancy|null
     */
    public function current(): ?Tenancy
    {
        return $this->current;
    }

    /**
     * Register a custom tenancy creator
     *
     * @param string                                                    $name
     * @param callable(array, string): \Tenanted\Core\Contracts\Tenancy $creator
     *
     * @return static
     */
    public function registerTenancy(string $name, callable $creator): static
    {
        $this->tenancyCreators[$name] = $creator;

        return $this;
    }

    /**
     * Stack a tenancy
     *
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return static
     */
    public function stackTenancy(Tenancy $tenancy): static
    {
        $this->tenancyStack[] = $this->current = $tenancy;

        return $this;
    }

    /**
     * Get the current tenancy stack
     *
     * @return list<\Tenanted\Core\Contracts\TenantProvider>
     */
    public function tenancyStack(): array
    {
        return $this->tenancyStack;
    }
}
<?php
declare(strict_types=1);

namespace Tenanted\Core\Providers;

use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Support\TenantEntity;

/**
 * Array Tenant Provider
 */
class ArrayTenantProvider implements TenantProvider
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var array<int, array>
     */
    private array $tenants;

    /**
     * @var array<mixed, int>
     */
    private array $tenantKeyMap = [];

    /**
     * @var array<string, int>
     */
    private array $tenantIdentifierMap = [];

    /**
     * @var string
     */
    private string $identifier;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var class-string<\Tenanted\Core\Contracts\Tenant>
     */
    private string $entity;

    /**
     * @param string                                        $name
     * @param array                                         $tenants
     * @param string                                        $identifier
     * @param string                                        $key
     * @param class-string<\Tenanted\Core\Contracts\Tenant> $entity
     */
    public function __construct(
        string $name,
        array  $tenants,
        string $identifier = 'identifier',
        string $key = 'id',
        string $entity = TenantEntity::class
    )
    {
        $this->name       = $name;
        $this->tenants    = array_values($tenants);
        $this->identifier = $identifier;
        $this->key        = $key;
        $this->entity     = $entity;

        $this->mapTenants();
    }

    private function mapTenants(): void
    {
        foreach ($this->tenants as $index => $tenant) {
            if (! isset($tenant[$this->identifier], $tenant[$this->key])) {
                // TODO: Throw an exception
            }

            $this->tenantIdentifierMap[$tenant[$this->identifier]] = $index;
            $this->tenantKeyMap[$tenant[$this->key]]               = $index;
        }
    }

    /**
     * @param int $index
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    private function makeEntity(int $index): ?Tenant
    {
        if (! isset($this->tenants[$index])) {
            return null;
        }

        $entity = $this->entity;

        return new $entity(
            $this->identifier,
            $this->key,
            $this->tenants[$index]
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function retrieveByIdentifier(string $identifier): ?Tenant
    {
        if (! isset($this->tenantIdentifierMap[$identifier])) {
            return null;
        }

        return $this->makeEntity($this->tenantIdentifierMap[$identifier]);
    }

    public function retrieveByKey(mixed $key): ?Tenant
    {
        if (! isset($this->tenantKeyMap[$key])) {
            return null;
        }

        return $this->makeEntity($this->tenantKeyMap[$key]);
    }

    public function retrieveBy(string $name, mixed $value): ?Tenant
    {
        foreach ($this->tenants as $index => $tenant) {
            if (isset($tenant[$name])) {
                if (
                    $tenant[$name] === $value
                    || ($value instanceof \Closure && $value($tenant[$name]))
                ) {
                    return $this->makeEntity($index);
                }
            }
        }

        return null;
    }
}
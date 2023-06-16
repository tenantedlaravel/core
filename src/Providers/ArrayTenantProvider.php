<?php
declare(strict_types=1);

namespace Tenanted\Core\Providers;

use Closure;
use RuntimeException;
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
     * @var array<int, array<string, mixed>>
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
     * @param array<array<string, mixed>>                   $tenants
     * @param string                                        $identifier
     * @param string                                        $key
     * @param class-string<\Tenanted\Core\Contracts\Tenant> $entity
     */
    public function __construct(
        string  $name,
        array   $tenants,
        ?string $identifier = 'identifier',
        ?string $key = 'id',
        ?string $entity = TenantEntity::class
    )
    {
        $this->name       = $name;
        $this->tenants    = array_values($tenants);
        $this->identifier = $identifier ?? 'identifier';
        $this->key        = $key ?? 'id';
        $this->entity     = $entity ?? TenantEntity::class;

        $this->mapTenants();
    }

    /**
     * @return void
     */
    private function mapTenants(): void
    {
        foreach ($this->tenants as $index => $tenant) {
            if (! isset($tenant[$this->identifier], $tenant[$this->key])) {
                throw new RuntimeException('Tenant missing identifier and/or key');
            }

            /**
             * @psalm-suppress MixedPropertyTypeCoercion
             * @psalm-suppress MixedArrayOffset
             */
            $this->tenantIdentifierMap[$tenant[$this->identifier]] = $index; // @phpstan-ignore-line
            /** @psalm-suppress MixedArrayOffset */
            $this->tenantKeyMap[$tenant[$this->key]] = $index;
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entity;
    }

    /**
     * @param string $identifier
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByIdentifier(string $identifier): ?Tenant
    {
        if (! isset($this->tenantIdentifierMap[$identifier])) {
            return null;
        }

        return $this->makeEntity($this->tenantIdentifierMap[$identifier]);
    }

    /**
     * @param mixed $key
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByKey(mixed $key): ?Tenant
    {
        /** @psalm-suppress MixedArrayOffset */
        if (! isset($this->tenantKeyMap[$key])) {
            return null;
        }

        return $this->makeEntity($this->tenantKeyMap[$key]);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveBy(string $name, mixed $value): ?Tenant
    {
        foreach ($this->tenants as $index => $tenant) {
            if (isset($tenant[$name])) {
                if (
                    $tenant[$name] === $value
                    || ($value instanceof Closure && $value($tenant[$name]))
                ) {
                    return $this->makeEntity($index);
                }
            }
        }

        return null;
    }
}
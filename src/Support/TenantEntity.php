<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Tenanted\Core\Contracts\Tenant;

/**
 * Default Tenant Entity
 */
class TenantEntity implements Tenant
{
    private string $identifier;

    private mixed $key;

    private array $attributes;

    public function __construct(string $identifier, mixed $key, array $attributes)
    {
        $this->identifier = $identifier;
        $this->key        = $key;
        $this->attributes = $attributes;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        // Intentionally empty
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function __unset(string $name): void
    {
        // Intentionally empty
    }

    public function getTenantIdentifier(): string
    {
        return $this->{$this->getTenantIdentifierName()};
    }

    public function getTenantIdentifierName(): string
    {
        return $this->identifier;
    }

    public function getTenantKey(): mixed
    {
        return $this->{$this->getTenantKeyName()};
    }

    public function getTenantKeyName(): string
    {
        return $this->key;
    }
}
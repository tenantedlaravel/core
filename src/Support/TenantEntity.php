<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Tenanted\Core\Contracts\Tenant;

/**
 * Default Tenant Entity
 */
class TenantEntity implements Tenant
{
    /**
     * @var string
     */
    private string $identifier;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var mixed[]
     */
    private array $attributes;

    /**
     * @param string  $identifier
     * @param string  $key
     * @param mixed[] $attributes
     */
    public function __construct(string $identifier, string $key, array $attributes)
    {
        $this->identifier = $identifier;
        $this->key        = $key;
        $this->attributes = $attributes;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        // Intentionally empty
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function __unset(string $name): void
    {
        // Intentionally empty
    }

    /**
     * @return string
     */
    public function getTenantIdentifier(): string
    {
        // @phpstan-ignore-next-line
        return (string) $this->__get($this->getTenantIdentifierName());
    }

    /**
     * @return string
     */
    public function getTenantIdentifierName(): string
    {
        return $this->identifier;
    }

    /**
     * @return mixed
     */
    public function getTenantKey(): mixed
    {
        return $this->__get($this->getTenantKeyName());
    }

    /**
     * @return string
     */
    public function getTenantKeyName(): string
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function isTenantActive(): bool
    {
        return (bool) $this->__get('active');
    }
}
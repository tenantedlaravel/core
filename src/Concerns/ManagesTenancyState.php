<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Tenanted\Core\Contracts\Tenant;
use Tenanted\Core\Events\TenancyChanged;

/**
 *
 */
trait ManagesTenancyState
{
    /**
     * @var \Tenanted\Core\Contracts\Tenant|null
     */
    private ?Tenant $tenant;

    /**
     * @var string|null
     */
    private ?string $via;

    /**
     * @var mixed
     */
    private mixed $viaValue;

    /**
     * @return bool
     */
    public function check(): bool
    {
        return $this->tenant() !== null;
    }

    /**
     * @return string|null
     */
    public function identifier(): ?string
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->check() ? $this->tenant()->getTenantIdentifier() : null;
    }

    /**
     * @return mixed
     */
    public function key(): mixed
    {
        /** @noinspection NullPointerExceptionInspection */
        return $this->check() ? $this->tenant()->getTenantKey() : null;
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return Tenancy
     */
    public function setTenant(?Tenant $tenant): static
    {
        $previous     = $this->tenant();
        $this->tenant = $tenant;

        if ($previous !== $tenant) {
            TenancyChanged::dispatch($tenant, $previous, $this);
        }

        return $this;
    }

    /**
     * Set the attribute used to find the tenant
     *
     * @param string $name
     *
     * @return static
     */
    protected function setVia(string $name, mixed $value): static
    {
        $this->via      = $name;
        $this->viaValue = $value;

        return $this;
    }

    /**
     * @return string|null
     */
    public function identifiedVia(): ?string
    {
        return $this->via;
    }

    /**
     * @return string|null
     */
    public function identifiedUsing(): mixed
    {
        return $this->viaValue;
    }

    /**
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }
}
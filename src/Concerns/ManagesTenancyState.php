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
    private ?Tenant $tenant = null;

    /**
     * @var string|null
     */
    private ?string $via = null;

    /**
     * @var mixed
     */
    private mixed $viaValue = null;

    /**
     * @return bool
     *
     * @phpstan-assert-if-true \Tenanted\Core\Contracts\Tenant $this->tenant()
     * @phpstan-assert-if-false null $this->tenant()
     * @phpstan-assert-if-true mixed $this->key()
     * @phpstan-assert-if-false null $this->key()
     * @phpstan-assert-if-true string $this->identifier()
     * @phpstan-assert-if-false null $this->identifier()
     *
     * @psalm-assert-if-true \Tenanted\Core\Contracts\Tenant $this->tenant()
     * @psalm-assert-if-false null $this->tenant()
     * @psalm-assert-if-true mixed $this->key()
     * @psalm-assert-if-false null $this->key()
     * @psalm-assert-if-true string $this->identifier()
     * @psalm-assert-if-false null $this->identifier()
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
        /**
         * @noinspection   NullPointerExceptionInspection
         * @psalm-suppress PossiblyNullReference
         */
        return $this->check() ? $this->tenant()->getTenantIdentifier() : null;
    }

    /**
     * @return mixed
     */
    public function key(): mixed
    {
        /**
         * @noinspection   NullPointerExceptionInspection
         * @psalm-suppress PossiblyNullReference
         */
        return $this->check() ? $this->tenant()->getTenantKey() : null;
    }

    /**
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return static
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
     *
     * @psalm-suppress MixedInferredReturnType
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
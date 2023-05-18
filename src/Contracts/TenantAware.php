<?php

namespace Tenanted\Core\Contracts;

/**
 * Tenant Aware Contract
 *
 * Marks a class as being "tenant-aware", meaning that when it is instantiated by
 * the container, the current tenant should be injected.
 */
interface TenantAware
{
    /**
     * Set the current tenant
     *
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return static
     */
    public function setTenant(?Tenant $tenant): static;

    /**
     * Get the current tenant
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function getTenant(): ?Tenant;

    /**
     * Determine whether there is a current tenant
     *
     * @return bool
     */
    public function hasTenant(): bool;
}
<?php

namespace Tenanted\Core\Contracts;

/**
 * Tenant Provider Contract
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
interface TenantProvider
{
    /**
     * Retrieve a tenant by identifier
     *
     * @param string $identifier
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByIdentifier(string $identifier): ?Tenant;

    /**
     * Retrieve a tenant by key
     *
     * @param mixed $key
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveByKey(mixed $key): ?Tenant;

    /**
     * Retrieve a tenant by a provided value/attribute
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function retrieveBy(string $name, mixed $value): ?Tenant;

    /**
     * Get the name the provider is registered as
     *
     * @return string
     */
    public function getName(): string;
}
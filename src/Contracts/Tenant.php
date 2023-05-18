<?php

namespace Tenanted\Core\Contracts;

/**
 * Tenant Contract
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
interface Tenant
{
    /**
     * Get the tenant identifier
     *
     * Returns the identifier that is used to externally identify the tenant.
     * The specific value returned by this method will depend on the method of
     * identification.
     *
     * @return string
     */
    public function getTenantIdentifier(): string;

    /**
     * Get the name of the tenant identifier
     *
     * Returns the name for the identifier, typically a property name,
     * attribute name or database column name.
     *
     * @return string
     */
    public function getTenantIdentifierName(): string;

    /**
     * Get the tenant key
     *
     * Returns the key that is used to internally identify the tenant. The
     * specific value returned by this method will depend on your implementation,
     * but will typically be the tenants' primary key, or some other unique,
     * internal identifier.
     *
     * @return mixed
     */
    public function getTenantKey(): mixed;

    /**
     * Get the name of the tenant key
     *
     * Returns the name for the key, typically a property name, attribute name
     * or database column name.
     *
     * @return string
     */
    public function getTenantKeyName(): string;
}
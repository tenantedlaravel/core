<?php

namespace Tenanted\Core\Contracts;

use Illuminate\Http\Request;

/**
 * Tenancy Contract
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
interface Tenancy
{
    /**
     * Get the name of the tenancy
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the tenancy config
     *
     * @return array<string, mixed>
     */
    public function config(): array;

    /**
     * Determine if there is a current tenant
     *
     * @return bool
     *
     * @phpstan-assert-if-true Tenant $this->tenant()
     * @phpstan-assert-if-false null $this->tenant()
     */
    public function check(): bool;

    /**
     * Get the current tenant
     *
     * @return \Tenanted\Core\Contracts\Tenant|null
     */
    public function tenant(): ?Tenant;

    /**
     * Get the key for the current tenant
     *
     * @return mixed
     */
    public function key(): mixed;

    /**
     * Get the identifier for the current tenant
     *
     * @return string|null
     */
    public function identifier(): ?string;

    /**
     * Set the current tenant
     *
     * @param \Tenanted\Core\Contracts\Tenant|null $tenant
     *
     * @return static
     */
    public function setTenant(?Tenant $tenant): static;

    /**
     * Identify and set the current tenant
     *
     * @param string      $identifier
     * @param string|null $name
     *
     * @return bool
     */
    public function identify(string $identifier, ?string $name = null): bool;

    /**
     * Load and set the current tenant
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function load(mixed $key): bool;

    /**
     * Get the tenant provider used by this tenancy
     *
     * @return \Tenanted\Core\Contracts\TenantProvider
     */
    public function provider(): TenantProvider;

    /**
     * Get the tenant resolver used by this tenancy
     *
     * @return \Tenanted\Core\Contracts\TenantResolver|null
     */
    public function resolver(): ?TenantResolver;

    /**
     * Use the provided tenant resolver
     *
     * @param \Tenanted\Core\Contracts\TenantResolver $resolver
     *
     * @return static
     */
    public function use(TenantResolver $resolver): static;

    /**
     * Get the name of the value used to resolve the current tenant
     *
     * @return string|null
     */
    public function identifiedVia(): ?string;

    /**
     * Get the value used to resolve the current tenant
     *
     * @return mixed
     */
    public function identifiedUsing(): mixed;

    /**
     * Resolve the tenancy for a request
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function resolve(Request $request): bool;
}
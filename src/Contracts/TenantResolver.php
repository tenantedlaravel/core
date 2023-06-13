<?php

namespace Tenanted\Core\Contracts;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteRegistrar;

/**
 * Tenant Resolver Contract
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
interface TenantResolver
{
    final const VALUE_IDENTIFIER = 'tenant_identifier';

    final const VALUE_KEY = 'tenant_key';

    /**
     * Get the name of the resolver
     *
     * @return string
     */
    public function name(): string;

    /**
     * @param \Illuminate\Http\Request         $request
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return bool
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function resolve(Request $request, Tenancy $tenancy): bool;

    /**
     * @param string|null $tenancy
     * @param string|null $value
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(?string $tenancy = null, ?string $value = null): RouteRegistrar;
}
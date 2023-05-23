<?php

namespace Tenanted\Core\Contracts;

use Closure;
use Illuminate\Http\Request;

/**
 * Acts as Middleware Contract
 *
 * Tenant resolvers that use this contract will be run by the middleware,
 * essentially proxying the middleware call.
 *
 * @see \Tenanted\Core\Http\Middleware\TenantedRoutes
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
interface ActsAsMiddleware
{
    /**
     * Handle a request as if this class were middleware
     *
     * @param \Illuminate\Http\Request         $request
     * @param \Closure                         $next
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     *
     * @return mixed
     */
    public function asMiddleware(Request $request, Closure $next, Tenancy $tenancy): mixed;
}
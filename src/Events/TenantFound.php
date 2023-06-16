<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;


/**
 * Tenant Found Event
 *
 * This is a base event class used by all events for when tenants are retrieved
 * from storage.
 *
 * @package        tenantedlaravel/core
 * @author         Ollie Read <code@ollie.codes>
 *
 * @method static static dispatch(Tenant $tenant, Tenancy $tenancy)
 * @method static static|void dispatchIf(bool $boolean, Tenant $tenant, Tenancy $tenancy)
 * @method static static|void dispatchUnless(bool $boolean, Tenant $tenant, Tenancy $tenancy)
 *
 * @psalm-suppress MethodSignatureMismatch
 * @psalm-suppress MoreSpecificImplementedParamType
 * @psalm-suppress ImplementedReturnTypeMismatch
 */
abstract class TenantFound extends TenancyEvent
{
    /**
     * @var \Tenanted\Core\Contracts\Tenant
     */
    private Tenant $tenant;

    /**
     * @param \Tenanted\Core\Contracts\Tenant  $tenant
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     */
    public function __construct(Tenant $tenant, Tenancy $tenancy)
    {
        parent::__construct($tenancy);
        $this->tenant = $tenant;
    }

    /**
     * @return \Tenanted\Core\Contracts\Tenant
     */
    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    /**
     * @return string
     *
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    public function via(): string
    {
        return $this->tenancy()->identifiedVia();
    }
}
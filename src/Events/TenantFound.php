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
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 *
 * @method static static dispatch(Tenant $tenant, Tenancy $tenancy)
 * @method static static dispatchIf(bool $boolean, Tenant $tenant, Tenancy $tenancy)
 * @method static static dispatchUnless(bool $boolean, Tenant $tenant, Tenancy $tenancy)
 */
abstract class TenantFound extends TenancyEvent
{
    /**
     * @var \Tenanted\Core\Contracts\Tenant
     */
    private Tenant $tenant;

    public function __construct(Tenant $tenant, Tenancy $tenancy)
    {
        parent::__construct($tenancy);
        $this->tenant = $tenant;
    }

    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    public function via(): string
    {
        return $this->tenancy()->identifiedVia();
    }
}
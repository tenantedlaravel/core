<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\Tenant;

/**
 * Tenancy Changed Event
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 *
 * @method static static dispatch(Tenant|null $current, Tenant|null $previous, Tenancy $tenancy)
 * @method static static dispatchIf(bool $boolean, Tenant|null $current, Tenant|null $previous, Tenancy $tenancy)
 * @method static static dispatchUnless(bool $boolean, Tenant|null $current, Tenant|null $previous, Tenancy $tenancy)
 */
class TenancyChanged extends TenancyEvent
{
    /**
     * @var \Tenanted\Core\Contracts\Tenant|null
     */
    private ?Tenant $current;

    /**
     * @var \Tenanted\Core\Contracts\Tenant|null
     */
    private ?Tenant $previous;

    public function __construct(?Tenant $current, ?Tenant $previous, Tenancy $tenancy)
    {
        parent::__construct($tenancy);

        $this->current  = $current;
        $this->previous = $previous;
    }

    public function current(): ?Tenant
    {
        return $this->current;
    }

    public function previous(): ?Tenant
    {
        return $this->previous;
    }
}
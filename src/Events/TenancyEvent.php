<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tenanted\Core\Contracts\Tenancy;

/**
 * Tenancy Event
 *
 * This is a base event class used by all events that are specific to a
 * tenancy.
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 *
 * @method static static dispatch(Tenancy $tenancy)
 * @method static static dispatchIf(bool $boolean, Tenancy $tenancy)
 * @method static static dispatchUnless(bool $boolean, Tenancy $tenancy)
 */
abstract class TenancyEvent
{
    use Dispatchable;

    /**
     * @var \Tenanted\Core\Contracts\Tenancy
     */
    private Tenancy $tenancy;

    /**
     * @param \Tenanted\Core\Contracts\Tenancy $tenancy
     */
    public function __construct(Tenancy $tenancy)
    {
        $this->tenancy = $tenancy;
    }

    /**
     * @return \Tenanted\Core\Contracts\Tenancy
     */
    public function tenancy(): Tenancy
    {
        return $this->tenancy;
    }
}
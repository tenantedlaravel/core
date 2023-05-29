<?php

namespace Tenanted\Core\Contracts;

use Illuminate\Contracts\Foundation\Application;

/**
 * Feature Contract
 *
 * This contract represents an application wide feature that should be activated
 * during the boot phase of the package.
 *
 * @package tenantedlaravel/core
 * @author  Ollie Read <code@ollie.codes>
 */
interface Feature
{
    /**
     * Initialise the feature
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    public function initialise(Application $app): void;
}
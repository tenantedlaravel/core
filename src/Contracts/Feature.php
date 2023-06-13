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
     * Set the application instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return static
     */
    public function setApplication(Application $app): static;

    /**
     * Initialise the feature
     *
     * @return void
     */
    public function initialise(): void;

    /**
     * Boot the feature
     *
     * @return void
     */
    public function boot(): void;
}
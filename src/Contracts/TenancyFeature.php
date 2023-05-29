<?php

namespace Tenanted\Core\Contracts;

use Illuminate\Contracts\Foundation\Application;

interface TenancyFeature
{
    public function initialise(Application $app, Tenancy $tenancy): void;
}
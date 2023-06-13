<?php
declare(strict_types=1);

namespace Tenanted\Core\Features;

use Illuminate\Contracts\Foundation\Application;
use Tenanted\Core\Contracts\Feature;

abstract class BaseFeature implements Feature
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected Application $app;

    public function setApplication(Application $app): static
    {
        $this->app = $app;

        return $this;
    }
}
<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Illuminate\Foundation\Application;
use Tenanted\Core\Concerns\ManagesTenantProviders;

/**
 *
 */
final class TenantedManager
{
    use ManagesTenantProviders;

    /**
     * @var \Illuminate\Foundation\Application
     */
    private Application $app;

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    protected function getDefaultProviderName(): string
    {
        return $this->app['config']['tenanted.defaults.provider'];
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    protected function getProviderConfig(string $name): ?array
    {
        return $this->app['config']['tenanted.providers.' . $name] ?? null;
    }
}
<?php
declare(strict_types=1);

namespace Tenanted\Core\Features;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tenanted\Core\Contracts\Feature;
use Tenanted\Core\Events\TenantFound;

/**
 * Not Found On Inactive Feature
 *
 * Feature that automatically throws an exception that will be converted
 * into a 404 response if a found tenant is inactive.
 */
class NotFoundOnInactive implements Feature
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function initialise(Application $app): void
    {
        $app->make(Dispatcher::class)
            ->listen(TenantFound::class, $this->notFoundIfInactive(...));
    }

    /**
     * Event listener
     *
     * @param \Tenanted\Core\Events\TenantFound $event
     *
     * @return void
     */
    protected function notFoundIfInactive(TenantFound $event): void
    {
        if (! $event->tenant()->isTenantActive()) {
            throw new NotFoundHttpException(
                sprintf('Tenant [%s] is not active', $event->tenant()->getTenantIdentifier())
            );
        }
    }
}
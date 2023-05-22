<?php

namespace Tenanted\Core\Listeners;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Tenanted\Core\Http\Middleware\TenantedRoutes;
use Tenanted\Core\TenantedManager;

class RouteMatchedListener
{
    /**
     * @var \Tenanted\Core\TenantedManager
     */
    private TenantedManager $manager;

    public function __construct(TenantedManager $manager)
    {
        $this->manager = $manager;
    }

    public function handle(RouteMatched $event): void
    {
        $routeMiddleware = app(Router::class)->resolveMiddleware($event->route->middleware(), $event->route->excludedMiddleware());

        foreach ($routeMiddleware as $middleware) {
            if (Str::startsWith($middleware, TenantedRoutes::class . ':')) {
                $this->handleFoundMiddleware($event, $middleware);
            }
        }

        $event->route->withoutMiddleware(TenantedRoutes::class);
    }

    private function handleFoundMiddleware(RouteMatched $event, mixed $middleware): void
    {
        [, $arguments] = explode(':', $middleware);
        [$resolverName, $tenancyName] = explode(',', $arguments);

        $tenancy = $this->manager->tenancy($tenancyName ?? null);

        if ($resolverName !== null) {
            $tenancy->use($this->manager->resolver($resolverName));
        }

        $tenancy->resolve($event->request);
    }
}

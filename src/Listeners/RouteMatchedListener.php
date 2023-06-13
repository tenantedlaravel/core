<?php

namespace Tenanted\Core\Listeners;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Tenanted\Core\Contracts\ActsAsMiddleware;
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
        $forget          = true;

        foreach ($routeMiddleware as $middleware) {
            if ($middleware === TenantedRoutes::class || Str::startsWith($middleware, TenantedRoutes::class . ':')) {
                if ($this->handleFoundMiddleware($event, $middleware)) {
                    $forget = false;
                }
            }
        }

        if ($forget) {
            $event->route->withoutMiddleware(TenantedRoutes::class);
        }
    }

    private function handleFoundMiddleware(RouteMatched $event, string $middleware): bool
    {
        [, $arguments] = explode(':', $middleware);
        [$tenancyName, $resolverName] = explode(',', $arguments);

        $tenancy = $this->manager->tenancy(empty($tenancyName) ? null : $tenancyName);
        $this->manager->stackTenancy($tenancy);

        if ($resolverName !== null) {
            $tenancy->use($this->manager->resolver($resolverName));
        }

        $tenancy->resolve($event->request);

        return $tenancy->resolver() instanceof ActsAsMiddleware;
    }
}

<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Tenanted\Core\Support\RouteHelper;

/**
 *
 */
class PathTenantResolver extends ParameterTenantResolver
{
    /**
     * @var int
     */
    private int $segment;

    /**
     * @param string      $name
     * @param int         $segment
     * @param string|null $prefix
     */
    public function __construct(string $name, int $segment = 0)
    {
        parent::__construct($name);

        $this->segment = $segment;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    protected function fallbackResolution(Request $request): ?string
    {
        return $request->segment($this->segment);
    }

    /**
     * @param string|null $tenancy
     * @param string|null $value
     *
     * @return \Illuminate\Routing\RouteRegistrar
     */
    public function routes(?string $tenancy = null, ?string $value = null): RouteRegistrar
    {
        return app(Router::class)
            ->middleware(RouteHelper::middleware($this->name(), $tenancy))
            ->prefix('/' . RouteHelper::parameter($this->name(), $tenancy, $value));
    }
}
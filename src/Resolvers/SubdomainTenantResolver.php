<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\Str;
use Tenanted\Core\Support\RouteHelper;

class SubdomainTenantResolver extends ParameterTenantResolver
{
    private string $domain;

    public function __construct(string $name, string $domain)
    {
        parent::__construct($name);

        $this->domain = $domain;
    }

    protected function fallbackResolution(Request $request): ?string
    {
        $host   = $request->getHost();
        $domain = '.' . $this->domain;

        if (Str::endsWith($host, $domain)) {
            return Str::before($host, $domain);
        }

        return null;
    }

    public function routes(?string $tenancy = null, ?string $value = null): RouteRegistrar
    {
        return app(Router::class)
            ->domain(RouteHelper::parameter($this->name(), $tenancy, $value) . '.' . $this->domain)
            ->middleware(RouteHelper::middleware($this->name(), $tenancy));
    }
}
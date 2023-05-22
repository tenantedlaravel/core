<?php
/** @noinspection PhpUnnecessaryStaticReferenceInspection */
declare(strict_types=1);

namespace Tenanted\Core;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Events\TenantIdentified;
use Tenanted\Core\Events\TenantLoaded;

/**
 *
 */
final class Tenancy implements Contracts\Tenancy
{
    use Concerns\TenancyHelpers,
        Concerns\ManagesTenancyState;

    /**
     * @param string                                  $name
     * @param \Tenanted\Core\Contracts\TenantProvider $provider
     * @param \Illuminate\Config\Repository           $config
     */
    public function __construct(string $name, TenantProvider $provider, Repository $config)
    {
        $this->name     = $name;
        $this->provider = $provider;
        $this->config   = $config;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     *
     * @throws \Tenanted\Core\Exceptions\TenantResolverException
     */
    public function resolve(Request $request): bool
    {
        $resolver = $this->resolver();

        if ($resolver === null) {
            $this->resolver = $resolver = tenanted()->resolver();
        }

        return $resolver->resolve($request, $this);
    }

    /**
     * @param string      $identifier
     * @param string|null $name
     *
     * @return bool
     */
    public function identify(string $identifier, ?string $name = null): bool
    {
        if ($name) {
            $tenant = $this->provider->retrieveBy($name, $identifier);
        } else {
            $tenant = $this->provider->retrieveByIdentifier($identifier);

            if ($tenant !== null) {
                $name = $tenant->getTenantIdentifierName();
            }
        }

        if ($tenant !== null) {
            $this->setVia($name, $identifier);
            TenantIdentified::dispatch($tenant, $this);
        }

        return $this->setTenant($tenant)->check();
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function load(mixed $key): bool
    {
        $tenant = $this->provider->retrieveByKey($key);

        if ($tenant !== null) {
            $this->setVia($tenant->getTenantKeyName(), $key);
            TenantLoaded::dispatch($tenant, $this);
        }

        return $this->setTenant($tenant)->check();
    }

}
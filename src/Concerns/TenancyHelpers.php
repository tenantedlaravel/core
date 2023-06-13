<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Illuminate\Config\Repository;
use Tenanted\Core\Contracts\TenantProvider;
use Tenanted\Core\Contracts\TenantResolver;

trait TenancyHelpers
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected Repository $config;

    /**
     * @var \Tenanted\Core\Contracts\TenantProvider
     */
    protected TenantProvider $provider;

    /**
     * @var \Tenanted\Core\Contracts\TenantResolver|null
     */
    protected ?TenantResolver $resolver;

    /**
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return $this->config->all();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return \Tenanted\Core\Contracts\TenantProvider
     */
    public function provider(): TenantProvider
    {
        return $this->provider;
    }

    /**
     * @param \Tenanted\Core\Contracts\TenantResolver $resolver
     *
     * @return static
     */
    public function use(TenantResolver $resolver): static
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @return \Tenanted\Core\Contracts\TenantResolver|null
     */
    public function resolver(): ?TenantResolver
    {
        return $this->resolver;
    }
}
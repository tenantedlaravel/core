<?php
declare(strict_types=1);

namespace Tenanted\Core\Resolvers;

use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\TenantResolver;

/**
 *
 */
abstract class BaseTenantResolver implements TenantResolver
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     *
     * @api
     */
    public function name(): string
    {
        return $this->name;
    }

    protected function handleIdentifier(Tenancy $tenancy, string $identifier, ?string $binding = null): bool
    {
        if ($binding === TenantResolver::VALUE_IDENTIFIER) {
            return $tenancy->identify($identifier);
        }

        if ($binding === TenantResolver::VALUE_KEY) {
            return $tenancy->load($identifier);
        }

        return $tenancy->identify($identifier, $binding);
    }
}
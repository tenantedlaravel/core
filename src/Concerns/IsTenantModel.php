<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \Tenanted\Core\Contracts\Tenant
 */
trait IsTenantModel
{
    public function getTenantIdentifier(): string
    {
        return $this->getAttribute($this->getTenantIdentifierName());
    }

    public function getTenantKey(): mixed
    {
        return $this->getKey();
    }

    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }
}
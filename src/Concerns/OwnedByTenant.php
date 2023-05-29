<?php
declare(strict_types=1);

namespace Tenanted\Core\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Events\QueuedClosure;
use RuntimeException;
use Tenanted\Core\Contracts\Tenancy;
use Tenanted\Core\Contracts\TenantRelationHandler;
use Tenanted\Core\Database\Relations\BelongsToHandler;
use Tenanted\Core\Database\Relations\BelongsToManyHandler;
use Tenanted\Core\Database\Relations\HasManyHandler;
use Tenanted\Core\Database\Relations\HasManyThroughHandler;
use Tenanted\Core\Database\Relations\HasOrMorphOneHandler;
use Tenanted\Core\Database\Relations\NoRelationHandler;
use Tenanted\Core\Database\Scopes\OwnedByTenantScope;
use Tenanted\Core\Support\TenantedHelper;
use Tenanted\Core\TenantedManager;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait OwnedByTenant
{
    /**
     * @var \Tenanted\Core\Contracts\TenantRelationHandler|null
     */
    protected static ?TenantRelationHandler $tenantRelationHandler = null;

    /**
     * @return void
     */
    public static function bootOwnedByTenant(): void
    {
        // Register this model event so that when it's booted, the tenant
        // relation handler is correctly created
        static::registerModelEvent('booted', self::bootedHandler(...));

        // Register the creating event, happening right before the data is persisted
        static::creating(self::creatingHandler(...));

        // Register the retrieved event, happening right after a model is hydrated
        static::retrieved(self::retrievedHandler(...));

        // Add the global scope to the model
        static::addGlobalScope(app(OwnedByTenantScope::class));
    }

    /**
     * Register a tenanted model event with the dispatcher.
     *
     * @param \Illuminate\Events\QueuedClosure|array|string|\Closure $callback
     *
     * @return void
     */
    public static function tenanted(QueuedClosure|array|string|Closure $callback): void
    {
        static::registerModelEvent('retrieved', $callback);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected static function bootedHandler(Model $model): void
    {
        /** @var \Tenanted\Core\Concerns\OwnedByTenant $model */
        if (! isset(self::$tenantRelationHandler)) {
            $relationName = $model->getTenantRelationName();

            if ($relationName === null) {
                self::$tenantRelationHandler = new NoRelationHandler();
            } else {
                if (! $model->isRelation($relationName)) {
                    throw new RuntimeException(
                        sprintf('Tenant relation [%s] on model [%s] is not a valid relation', $relationName, $model::class)
                    );
                }

                $relation = $model->{$relationName}();

                if ($relation instanceof BelongsTo) {
                    self::$tenantRelationHandler = new BelongsToHandler();
                } else if ($relation instanceof BelongsToMany) {
                    self::$tenantRelationHandler = new BelongsToManyHandler();
                } else if ($relation instanceof HasOne || $relation instanceof MorphOne) {
                    self::$tenantRelationHandler = new HasOrMorphOneHandler();
                } else if ($relation instanceof HasOneOrMany) {
                    self::$tenantRelationHandler = new HasManyHandler();
                } else if ($relation instanceof HasManyThrough) {
                    self::$tenantRelationHandler = new HasManyThroughHandler();
                }
            }

            // Fire an event to signify that a model has been set up as
            // being 'tenanted'
            $model->fireModelEvent('tenanted', false);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected static function creatingHandler(Model $model): void
    {
        $tenancy = app(TenantedManager::class)->current();

        if ($tenancy !== null) {
            self::$tenantRelationHandler->populateForCreation(
                $model,
                $tenancy
            );
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    protected static function retrievedHandler(Model $model): void
    {
        $tenancy = app(TenantedManager::class)->current();

        if ($tenancy !== null) {
            self::$tenantRelationHandler->populateAfterLoading(
                $model,
                $tenancy
            );
        }
    }

    /**
     * @return \Tenanted\Core\Contracts\Tenancy
     */
    private function getCurrentTenancy(): Tenancy
    {
        $tenancy = app(TenantedManager::class)->current();

        if ($tenancy === null) {
            throw new RuntimeException('No tenancy');
        }

        return $tenancy;
    }

    /**
     * @return string|null
     */
    public function getTenantRelatedKeyName(): ?string
    {
        $relationName = $this->getTenantRelationName();

        if ($this->isRelation($relationName)) {
            $relation = $this->{$relationName}();

            if (method_exists($relationName, 'getForeignKeyName')) {
                return $relation->getForeignKeyName();
            }
        }

        return TenantedHelper::tenantRelatedKeyName($this->getCurrentTenancy());
    }

    /**
     * @return string|null
     */
    public function getTenantRelationName(): ?string
    {
        return $this->getCurrentTenancy()->name();
    }

    /**
     * @return \Tenanted\Core\Contracts\TenantRelationHandler|null
     */
    public function getTenantRelationHandler(): ?TenantRelationHandler
    {
        return self::$tenantRelationHandler;
    }
}
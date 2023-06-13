<?php

namespace Tenanted\Core;

/**
 * @return \Tenanted\Core\TenantedManager
 */
function tenanted(): TenantedManager
{
    return app()->make(TenantedManager::class);
}
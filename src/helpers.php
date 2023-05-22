<?php

namespace Tenanted\Core;

/**
 * @return \Tenanted\Core\TenantedManager
 */
function tenanted(): TenantedManager
{
    return app(TenantedManager::class);
}
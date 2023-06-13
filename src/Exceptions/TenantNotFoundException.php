<?php
declare(strict_types=1);

namespace Tenanted\Core\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class TenantNotFoundException extends TenantedException implements Responsable
{
    public function toResponse($request): Response
    {
        return response('', 404);
    }
}
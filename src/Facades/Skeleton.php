<?php

namespace AungKhant\HttpSecurityHeaders\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AungKhant\HttpSecurityHeaders\Skeleton
 */
class Skeleton extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AungKhant\HttpSecurityHeaders\Skeleton::class;
    }
}

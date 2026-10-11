<?php

namespace AungKhant\HttpSecurityHeaders\Tests;

use AungKhant\HttpSecurityHeaders\SecurityHeadersServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SecurityHeadersServiceProvider::class];
    }
}

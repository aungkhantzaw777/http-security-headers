<?php

namespace AungKhant\HttpSecurityHeaders\Tests\Feature;

use AungKhant\HttpSecurityHeaders\SecurityHeadersManager;
use AungKhant\HttpSecurityHeaders\SecurityHeadersServiceProvider;
use AungKhant\HttpSecurityHeaders\Tests\TestCase;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\ServiceProvider;

final class ServiceProviderTest extends TestCase
{
    public function test_provider_boots_merges_defaults_and_registers_singleton(): void
    {
        self::assertTrue($this->app->isBooted());
        self::assertSame(require __DIR__.'/../../config/security-headers.php', config('security-headers'));
        self::assertSame($this->app->make(SecurityHeadersManager::class), $this->app->make(SecurityHeadersManager::class));
    }

    public function test_application_overrides_survive_config_merging(): void
    {
        config()->set('security-headers', ['preset' => 'api', 'headers' => ['X-Custom' => 'yes']]);
        (new SecurityHeadersServiceProvider($this->app))->register();

        self::assertTrue(config('security-headers.enabled'));
        self::assertSame([], config('security-headers.remove'));
        self::assertSame('api', config('security-headers.preset'));
        self::assertSame('yes', $this->app->make(SecurityHeadersManager::class)->headers()['X-Custom']);
    }

    public function test_config_can_be_published(): void
    {
        $paths = ServiceProvider::pathsToPublish(SecurityHeadersServiceProvider::class, 'security-headers-config');
        self::assertCount(1, $paths);
        self::assertSame(config_path('security-headers.php'), array_values($paths)[0]);
        self::assertFileExists(array_keys($paths)[0]);

        $target = config_path('security-headers.php');
        $original = is_file($target) ? file_get_contents($target) : null;

        try {
            $this->artisan('vendor:publish', ['--tag' => 'security-headers-config', '--force' => true])->assertSuccessful();
            self::assertSame(file_get_contents(array_keys($paths)[0]), file_get_contents($target));
        } finally {
            if ($original === null) {
                @unlink($target);
            } else {
                file_put_contents($target, $original);
            }
        }
    }

    public function test_explicitly_disabled_configuration_survives_merging(): void
    {
        config()->set('security-headers', ['enabled' => false, 'headers' => ['X-Frame-Options' => false]]);
        (new SecurityHeadersServiceProvider($this->app))->register();

        self::assertFalse(config('security-headers.enabled'));
        self::assertSame(['X-Frame-Options' => false], config('security-headers.headers'));
        self::assertSame([], $this->app->make(SecurityHeadersManager::class)->headers());
    }

    public function test_composer_metadata_is_discoverable_by_laravel(): void
    {
        $composer = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('aungkhantzaw777/http-security-headers', $composer['name']);
        self::assertSame([SecurityHeadersServiceProvider::class], $composer['extra']['laravel']['providers']);

        $directory = sys_get_temp_dir().'/security-headers-'.bin2hex(random_bytes(8));
        mkdir($directory.'/vendor/composer', 0777, true);
        file_put_contents($directory.'/composer.json', '{}');
        file_put_contents($directory.'/vendor/composer/installed.json', json_encode(['packages' => [$composer]], JSON_THROW_ON_ERROR));

        try {
            $manifest = new PackageManifest($this->app['files'], $directory, $directory.'/packages.php');
            self::assertContains(SecurityHeadersServiceProvider::class, $manifest->providers());
        } finally {
            $this->app['files']->deleteDirectory($directory);
        }
    }
}

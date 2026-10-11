<?php

namespace AungKhant\HttpSecurityHeaders\Tests\Unit;

use AungKhant\HttpSecurityHeaders\Enums\SecurityPreset;
use AungKhant\HttpSecurityHeaders\SecurityHeadersManager;
use AungKhant\HttpSecurityHeaders\Support\HeaderResolver;
use Illuminate\Config\Repository;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ValueError;

final class SecurityHeadersManagerTest extends TestCase
{
    public function test_default_preset(): void
    {
        self::assertSame([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ], (new SecurityHeadersManager(new Repository))->headers());
    }

    public function test_strict_and_api_presets(): void
    {
        $resolver = new HeaderResolver;
        $api = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
        ];
        self::assertSame($api, $resolver->resolve('api'));
        self::assertSame([...$api, 'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()'], $resolver->resolve(SecurityPreset::Strict));
    }

    public function test_overrides_disable_headers_and_removal_wins_case_insensitively(): void
    {
        $manager = new SecurityHeadersManager(new Repository(['security-headers' => [
            'headers' => [
                'x-frame-options' => 'DENY',
                'REFERRER-POLICY' => false,
                'X-Content-Type-Options' => null,
                'X-Custom' => 'first',
                'x-custom' => 'last',
                'X-Remove' => 'value',
            ],
            'remove' => ['x-remove'],
        ]]));

        self::assertSame(['X-Frame-Options' => 'DENY', 'x-custom' => 'last'], $manager->headers());
        self::assertSame(['x-remove'], $manager->headersToRemove());
    }

    public function test_disabled_configuration_bypasses_resolution_and_removal(): void
    {
        $manager = new SecurityHeadersManager(new Repository(['security-headers' => [
            'enabled' => false, 'preset' => 'invalid', 'remove' => ['X-Powered-By'],
        ]]));
        self::assertFalse($manager->enabled());
        self::assertSame([], $manager->headers());
        self::assertSame([], $manager->headersToRemove());
    }

    public function test_config_changes_are_observed_without_recreating_manager(): void
    {
        $config = new Repository;
        $manager = new SecurityHeadersManager($config);
        self::assertSame('SAMEORIGIN', $manager->headers()['X-Frame-Options']);
        $config->set('security-headers.preset', SecurityPreset::Strict);
        self::assertSame('DENY', $manager->headers()['X-Frame-Options']);
        $config->set('security-headers.enabled', false);
        self::assertSame([], $manager->headers());
    }

    public function test_invalid_preset_is_not_silently_ignored(): void
    {
        $this->expectException(ValueError::class);
        (new HeaderResolver)->resolve('strcit');
    }

    public function test_resolver_does_not_leak_overrides_between_calls(): void
    {
        $resolver = new HeaderResolver;
        $resolver->resolve('strict', ['X-Custom' => 'value'], ['X-Content-Type-Options']);

        self::assertSame([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ], $resolver->resolve('basic'));
    }

    #[DataProvider('invalidOverrides')]
    public function test_invalid_headers_are_rejected(array $headers): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new HeaderResolver)->resolve('basic', $headers);
    }

    public static function invalidOverrides(): array
    {
        return [
            [['Bad Name' => 'value']],
            [["X-Test\r\nInjected" => 'value']],
            [['X-Test' => "value\r\nInjected: yes"]],
            [['X-Test' => true]],
            [['X-Test' => 123]],
            [['X-Test' => ['value']]],
            [[0 => 'value']],
        ];
    }

    #[DataProvider('invalidRemovals')]
    public function test_invalid_removal_configuration_is_rejected(mixed $remove): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new SecurityHeadersManager(new Repository(['security-headers' => ['remove' => $remove]])))->headers();
    }

    public static function invalidRemovals(): array
    {
        return [['X-Test'], [['X-Test' => true]], [[false]], [["Bad\nName"]]];
    }
}

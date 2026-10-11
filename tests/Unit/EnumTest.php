<?php

namespace AungKhant\HttpSecurityHeaders\Tests\Unit;

use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;
use AungKhant\HttpSecurityHeaders\Enums\SecurityPreset;
use PHPUnit\Framework\TestCase;
use ValueError;

final class EnumTest extends TestCase
{
    public function test_existing_header_api_is_unchanged(): void
    {
        $expected = [
            'ContentSecurityPolicy' => 'Content-Security-Policy',
            'ContentSecurityPolicyReportOnly' => 'Content-Security-Policy-Report-Only',
            'CrossOriginEmbedderPolicy' => 'Cross-Origin-Embedder-Policy',
            'CrossOriginEmbedderPolicyReportOnly' => 'Cross-Origin-Embedder-Policy-Report-Only',
            'CrossOriginOpenerPolicy' => 'Cross-Origin-Opener-Policy',
            'CrossOriginOpenerPolicyReportOnly' => 'Cross-Origin-Opener-Policy-Report-Only',
            'CrossOriginResourcePolicy' => 'Cross-Origin-Resource-Policy',
            'PermissionsPolicy' => 'Permissions-Policy',
            'ReferrerPolicy' => 'Referrer-Policy',
            'StrictTransportSecurity' => 'Strict-Transport-Security',
            'XContentTypeOptions' => 'X-Content-Type-Options',
            'XFrameOptions' => 'X-Frame-Options',
        ];

        self::assertSame($expected, array_column(SecurityHeader::cases(), 'value', 'name'));

        foreach ($expected as $name => $value) {
            self::assertSame($name, SecurityHeader::from($value)->name);
            self::assertSame(SecurityHeader::from($value), SecurityHeader::tryFrom($value));
        }

        self::assertNull(SecurityHeader::tryFrom('Unknown-Header'));
        self::assertNull(SecurityHeader::tryFrom('content-security-policy'));
    }

    public function test_unknown_header_throws(): void
    {
        $this->expectException(ValueError::class);
        SecurityHeader::from('Unknown-Header');
    }

    public function test_preset_values(): void
    {
        self::assertSame(['Basic' => 'basic', 'Strict' => 'strict', 'Api' => 'api'], array_column(SecurityPreset::cases(), 'value', 'name'));
    }
}

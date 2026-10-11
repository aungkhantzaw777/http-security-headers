<?php

namespace AungKhant\HttpSecurityHeaders\Support;

use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;
use AungKhant\HttpSecurityHeaders\Enums\SecurityPreset;
use InvalidArgumentException;

final class HeaderResolver
{
    /**
     * @param  array<array-key, mixed>  $overrides  Untrusted configuration, validated before use.
     * @param  list<string>  $remove
     * @return array<string, string>
     */
    public function resolve(SecurityPreset|string $preset, array $overrides = [], array $remove = []): array
    {
        $preset = is_string($preset) ? SecurityPreset::from($preset) : $preset;
        $headers = new HeaderBag;

        foreach ($this->presetHeaders($preset) as $name => $value) {
            $headers->set($name, $value);
        }

        foreach ($overrides as $name => $value) {
            if (! is_string($name) || (! is_string($value) && $value !== false && $value !== null)) {
                throw new InvalidArgumentException('Header overrides must map header names to strings, false, or null.');
            }

            $headers->set($name, $value);
        }

        foreach ($remove as $name) {
            $headers->set($name, null);
        }

        return $headers->all();
    }

    /** @return array<string, string> */
    private function presetHeaders(SecurityPreset $preset): array
    {
        return [
            SecurityHeader::XContentTypeOptions->value => 'nosniff',
            SecurityHeader::XFrameOptions->value => $preset === SecurityPreset::Basic ? 'SAMEORIGIN' : 'DENY',
            SecurityHeader::ReferrerPolicy->value => $preset === SecurityPreset::Basic ? 'strict-origin-when-cross-origin' : 'no-referrer',
            ...($preset === SecurityPreset::Strict ? [
                SecurityHeader::PermissionsPolicy->value => 'camera=(), microphone=(), geolocation=()',
            ] : []),
        ];
    }
}

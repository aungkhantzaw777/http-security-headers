<?php

namespace AungKhant\HttpSecurityHeaders;

use AungKhant\HttpSecurityHeaders\Enums\SecurityPreset;
use AungKhant\HttpSecurityHeaders\Support\HeaderBag;
use AungKhant\HttpSecurityHeaders\Support\HeaderResolver;
use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

final class SecurityHeadersManager
{
    public function __construct(
        private readonly Repository $config,
        private readonly HeaderResolver $resolver = new HeaderResolver,
    ) {}

    public function enabled(): bool
    {
        return (bool) $this->config->get('security-headers.enabled', true);
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        return $this->resolver->resolve(
            $this->config->get('security-headers.preset', SecurityPreset::Basic->value),
            $this->config->get('security-headers.headers', []),
            $this->headersToRemove(),
        );
    }

    /** @return list<string> */
    public function headersToRemove(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $names = $this->config->get('security-headers.remove', []);

        if (! is_array($names) || ! array_is_list($names)) {
            throw new InvalidArgumentException('Headers to remove must be a list of header names.');
        }

        foreach ($names as $name) {
            if (! is_string($name)) {
                throw new InvalidArgumentException('Headers to remove must be strings.');
            }

            HeaderBag::validateName($name);
        }

        return $names;
    }
}

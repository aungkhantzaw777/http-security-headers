<?php

namespace AungKhant\HttpSecurityHeaders\Support;

use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;
use InvalidArgumentException;

/** A case-insensitive collection of single-value response headers. */
final class HeaderBag
{
    /** @var array<string, array{name: string, value: string}> */
    private array $headers = [];

    public static function validateName(string $name): void
    {
        if (! preg_match('/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/D', $name)) {
            throw new InvalidArgumentException('Invalid HTTP header name.');
        }
    }

    public function set(string $name, string|false|null $value): void
    {
        self::validateName($name);
        $key = strtolower($name);

        if ($value === false || $value === null) {
            unset($this->headers[$key]);

            return;
        }

        if (preg_match('/[\x00-\x08\x0A-\x1F\x7F]/', $value)) {
            throw new InvalidArgumentException('Invalid HTTP header value.');
        }

        foreach (SecurityHeader::cases() as $header) {
            if (strtolower($header->value) === $key) {
                $name = $header->value;
                break;
            }
        }

        $this->headers[$key] = ['name' => $name, 'value' => $value];
    }

    /** @return array<string, string> */
    public function all(): array
    {
        $headers = [];

        foreach ($this->headers as $header) {
            $headers[$header['name']] = $header['value'];
        }

        return $headers;
    }
}

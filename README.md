# HTTP Security Headers

A PHP string-backed enum for common HTTP security header names. Use `SecurityHeader` to reference header names in your application; you supply the header values and apply them to responses.

## Requirements

- PHP 8.4 or later (8.x)
- Illuminate Contracts 11, 12, or 13 (installed through Composer)

## Installation

```bash
composer require aungkhantzaw777/http-security-header
```

## Usage

Import the enum and use `->value` to get the HTTP header name:

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;

$header = SecurityHeader::ContentSecurityPolicy;

echo $header->value; // Content-Security-Policy
```

### Laravel response

Pass enum values as header names when building a response:

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;

return response('Hello, world!')->withHeaders([
    SecurityHeader::XContentTypeOptions->value => 'nosniff',
    SecurityHeader::XFrameOptions->value => 'DENY',
    SecurityHeader::ReferrerPolicy->value => 'strict-origin-when-cross-origin',
]);
```

### Convert a header name to an enum

Use `from()` for a known value or `tryFrom()` when the value might be unsupported:

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;

$header = SecurityHeader::from('Content-Security-Policy');
// SecurityHeader::ContentSecurityPolicy

$header = SecurityHeader::tryFrom('Unknown-Header');
// null
```

Enum lookup is case-sensitive and requires the exact spelling listed below. `from()` throws a `ValueError` for an unsupported value.

### List all headers

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;

foreach (SecurityHeader::cases() as $header) {
    echo $header->value . PHP_EOL;
}
```

## Available headers

| Enum case | Header name |
| --- | --- |
| `ContentSecurityPolicy` | `Content-Security-Policy` |
| `ContentSecurityPolicyReportOnly` | `Content-Security-Policy-Report-Only` |
| `CrossOriginEmbedderPolicy` | `Cross-Origin-Embedder-Policy` |
| `CrossOriginEmbedderPolicyReportOnly` | `Cross-Origin-Embedder-Policy-Report-Only` |
| `CrossOriginOpenerPolicy` | `Cross-Origin-Opener-Policy` |
| `CrossOriginOpenerPolicyReportOnly` | `Cross-Origin-Opener-Policy-Report-Only` |
| `CrossOriginResourcePolicy` | `Cross-Origin-Resource-Policy` |
| `PermissionsPolicy` | `Permissions-Policy` |
| `ReferrerPolicy` | `Referrer-Policy` |
| `StrictTransportSecurity` | `Strict-Transport-Security` |
| `XContentTypeOptions` | `X-Content-Type-Options` |
| `XFrameOptions` | `X-Frame-Options` |

## Testing

```bash
composer test
```

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.

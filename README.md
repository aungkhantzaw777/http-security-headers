# HTTP Security Headers

A Laravel security headers toolkit with configurable presets, opt-in response middleware, and reusable string-backed header enums. The existing `SecurityHeader` enum API is unchanged.

## Requirements

- PHP 8.3 or later (8.x)
- Laravel 12 or Laravel 13

The enum itself can still be used without booting Laravel. Installing the package now also installs its Illuminate dependencies.

## Installation

```bash
composer require aungkhantzaw777/http-security-headers
```

## Laravel setup

Laravel automatically discovers `SecurityHeadersServiceProvider`. Publish the optional configuration:

```bash
php artisan vendor:publish --tag=security-headers-config
```

Add the middleware to the existing `withMiddleware` callback in `bootstrap/app.php`:

```php
use AungKhant\HttpSecurityHeaders\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Configuration\Middleware;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(SecurityHeaders::class);
})
```

Alternatively, apply `SecurityHeaders::class` to specific routes with `->middleware(SecurityHeaders::class)`. Middleware is not added automatically.

### Configuration

```php
return [
    'enabled' => true,
    'preset' => 'basic',
    'headers' => [
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => false,
    ],
    'remove' => ['X-Powered-By'],
];
```

Header names are matched case-insensitively. String overrides replace preset values; `false` or `null` omits a header from the resolved set. Omission preserves any header already supplied by the application. Use `remove` to explicitly strip an existing response header; removal takes precedence over presets and overrides. Removal only affects headers present on the Laravel response, not headers added later by a web server or proxy.

When `enabled` is `false`, middleware leaves the response unchanged, including headers in `remove`. When enabled, resolved headers replace matching response headers. Invalid preset names and malformed header names or values raise exceptions.

### Presets

| Header | `basic` | `strict` | `api` |
| --- | --- | --- | --- |
| X-Content-Type-Options | nosniff | nosniff | nosniff |
| X-Frame-Options | SAMEORIGIN | DENY | DENY |
| Referrer-Policy | strict-origin-when-cross-origin | no-referrer | no-referrer |
| Permissions-Policy | — | camera=(), microphone=(), geolocation=() | — |

`basic` is the default. `strict` disables framing and access to the listed browser features; choose it explicitly when those restrictions suit your app. `api` uses the restrictive framing and referrer settings without browser feature restrictions. No preset adds CSP, HSTS, or cross-origin isolation headers. Configure these explicitly to match your application and deployment.

### Resolve headers directly

```php
use AungKhant\HttpSecurityHeaders\SecurityHeadersManager;

$headers = app(SecurityHeadersManager::class)->headers();
// array<string, string>, or [] when disabled
```

For resolution without a Laravel container:

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityPreset;
use AungKhant\HttpSecurityHeaders\Support\HeaderResolver;

$headers = (new HeaderResolver)->resolve(
    SecurityPreset::Basic,
    overrides: ['X-Frame-Options' => 'DENY'],
    remove: ['Referrer-Policy'],
);
```

## Enum usage

Import the enum and use `->value` to get the HTTP header name:

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;

$header = SecurityHeader::ContentSecurityPolicy;

echo $header->value; // Content-Security-Policy
```

For standalone PHP scripts, load Composer's autoloader before using the enum:

```php
require __DIR__ . '/vendor/autoload.php';
```

### PHP response

Use the enum value with PHP's `header()` function before sending output:

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;

header(SecurityHeader::XContentTypeOptions->value . ': nosniff');
```

### Laravel response

In a Laravel application, pass enum values as header names when building a response:

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

## Development

```bash
composer install
composer test
composer analyse
vendor/bin/pint --test
```

Tests cover the enum API, preset resolution, overrides, provider registration, configuration publishing, discovery metadata, and middleware responses. CI runs supported PHP versions against Laravel 12 and 13 with Testbench 10 and 11 respectively.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.

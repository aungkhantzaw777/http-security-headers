# Laravel HTTP Security Headers

A Laravel security headers toolkit with configurable presets, opt-in response middleware, and reusable string-backed header enums. The existing `SecurityHeader` enum API is unchanged.

## Requirements

- PHP 8.3 or later (8.x)
- Laravel 12 or Laravel 13

The enum itself can still be used without booting Laravel. Installing the package now also installs its Illuminate dependencies.

## Installation

```bash
composer require aungkhantzaw777/http-security-headers
```

## Quick start

Laravel automatically discovers `SecurityHeadersServiceProvider`. Enable the middleware to apply the default `basic` preset.

Add `SecurityHeaders::class` to the existing `withMiddleware` callback in `bootstrap/app.php`. A minimal example looks like this; keep your application's existing routing and middleware configuration:

```php
use AungKhant\HttpSecurityHeaders\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
    })
    ->create();
```

### Apply to specific routes

For selected routes, use route middleware instead of registering it globally:

```php
use AungKhant\HttpSecurityHeaders\Http\Middleware\SecurityHeaders;
use Illuminate\Support\Facades\Route;

Route::get('/status', fn () => response()->json(['status' => 'ok']))
    ->middleware(SecurityHeaders::class);
```

Installing the package alone does not change response headers. Middleware registration is opt-in.

### Manual provider registration

If your application disables package discovery, add this entry to the array in `bootstrap/providers.php`:

```php
AungKhant\HttpSecurityHeaders\SecurityHeadersServiceProvider::class,
```

## Configuration

Publish `config/security-headers.php` when you want to customize the defaults:

```bash
php artisan vendor:publish --tag=security-headers-config
```

The default configuration is:

```php
return [
    'enabled' => true,
    'preset' => 'basic',
    'headers' => [],
    'remove' => [],
];
```

### Override or disable headers

Use string header names or the existing enum's `->value` as configuration keys:

```php
use AungKhant\HttpSecurityHeaders\Enums\SecurityHeader;
use AungKhant\HttpSecurityHeaders\Enums\SecurityPreset;

return [
    'enabled' => true,
    'preset' => SecurityPreset::Basic->value,
    'headers' => [
        SecurityHeader::XFrameOptions->value => 'DENY',
        SecurityHeader::ReferrerPolicy->value => false,
        'X-Custom-Header' => 'example',
    ],
    'remove' => ['X-Powered-By'],
];
```

Header names are matched case-insensitively. String overrides replace preset values; `false` or `null` omits a header from the resolved set. Omission preserves any header already supplied by the application. Use `remove` to explicitly strip an existing response header; removal takes precedence over presets and overrides. Removal only affects headers present on the Laravel response, not headers added later by a web server or proxy.

When `enabled` is `false`, middleware leaves the response unchanged, including headers in `remove`. When enabled, resolved headers replace matching response headers. Invalid preset names and malformed header names or values raise exceptions.

## Presets

Select `basic`, `strict`, or `api` in configuration. The corresponding enum cases are `SecurityPreset::Basic`, `SecurityPreset::Strict`, and `SecurityPreset::Api`.

| Header | `basic` | `strict` | `api` |
| --- | --- | --- | --- |
| X-Content-Type-Options | nosniff | nosniff | nosniff |
| X-Frame-Options | SAMEORIGIN | DENY | DENY |
| Referrer-Policy | strict-origin-when-cross-origin | no-referrer | no-referrer |
| Permissions-Policy | — | camera=(), microphone=(), geolocation=() | — |

`basic` is the default. `strict` disables framing and access to the listed browser features; choose it explicitly when those restrictions suit your app. `api` uses the restrictive framing and referrer settings without browser feature restrictions. No preset adds CSP, HSTS, or cross-origin isolation headers. Configure these explicitly to match your application and deployment.

## Resolve headers directly

Resolve the singleton manager from Laravel's container or inject it into a controller or service:

```php
use AungKhant\HttpSecurityHeaders\SecurityHeadersManager;

$manager = app(SecurityHeadersManager::class);

$manager->enabled();         // Whether the toolkit is enabled.
$manager->headers();         // Final array<string, string>; [] when disabled.
$manager->headersToRemove(); // Configured list<string>; [] when disabled.
```

The manager reads the current `security-headers` configuration on each call. Resolving headers does not modify a response; middleware applies them.

For resolution without a Laravel container, pass a preset and optional overrides directly. The resolver does not read application configuration or the `enabled` setting:

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

The namespace, cases, and string values of `AungKhant\HttpSecurityHeaders\Enums\SecurityHeader` remain compatible with existing enum usage.

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

Run `composer format` to apply the package's PHP formatting rules.

Tests cover the enum API, preset resolution, overrides, provider registration, configuration publishing, discovery metadata, and middleware responses. CI runs supported PHP versions against Laravel 12 and 13 with Testbench 10 and 11 respectively.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.

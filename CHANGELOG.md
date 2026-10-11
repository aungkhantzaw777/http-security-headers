# Changelog

All notable changes to `http-security-headers` will be documented in this file.

## Unreleased

- Add Laravel 12 and 13 support with PHP 8.3+, provider discovery, and publishable configuration.
- Add Basic, Strict, and Api presets, case-insensitive overrides, and explicit header removal.
- Add an injectable security headers manager and opt-in response middleware.
- Preserve the existing `SecurityHeader` enum namespace, cases, and values.
- Replace template tests with PHPUnit and Orchestra Testbench coverage.

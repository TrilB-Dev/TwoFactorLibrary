# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-09-22

### Added
- Initial public release of the custom two-factor authentication library.
- TOTP generation, validation, and provisioning URI support.
- QR code generation from otpauth URIs using GD.
- Recovery code creation and validation helpers.
- Passkey-style WebAuthn challenge helpers for registration and authentication flows.
- Central package entry point via `TwoFactorAuthentication`.
- Documentation set covering each module and usage examples.
- Demo script demonstrating the library in practice.

### Changed
- Split the package into modular providers under `src/Modules` for cleaner maintenance.
- Improved Passkey implementation toward WebAuthn-style server flow patterns.
- Refreshed project README and per-module docs to match the implemented API.

### Fixed
- Resolved BOM/strict_types parsing issues affecting PHP execution.
- Stabilized support for QR, TOTP, and passkey verification helpers.
- Corrected documentation and examples to align with the actual runtime behavior.

## [Unreleased]

- Placeholder for future enhancements and bugfixes.

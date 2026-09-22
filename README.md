# TwoFactorLibrary

A custom PHP library for multi-factor authentication built without third-party 2FA dependencies.

## Features

- TOTP generation and validation
- QR code generation for otpauth URIs
- passkey-style challenge and signature validation
- recovery-code generation and validation
- configurable TOTP options for digits, period, and hash algorithm

## Installation

```bash
composer require trilbdev/two-factor-library
```

## Composer autoloading

The package uses PSR-4 autoloading:

```json
{
  "autoload": {
    "psr-4": {
      "Trilbdev\\TwoFactorLibrary\\": "src/"
    }
  }
}
```

## Quick start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');

$secret = $auth->totp()->generateSecret();
$code = $auth->totp()->createCode($secret);

$valid = $auth->totp()->validateCode($secret, $code);

if ($valid) {
    echo 'Code is valid.';
}
```

## Main entry point

The project exposes the main class `TwoFactorAuthentication`, which groups all available modules:

```php
$auth = new TwoFactorAuthentication('Example', 'user@example.com');

$auth->totp();
$auth->qrCode();
$auth->passkey();
$auth->recoveryCodes();
```

There is also a convenience alias:

```php
use Trilbdev\TwoFactorLibrary\TwoFactor;

$auth = new TwoFactor('Example', 'user@example.com');
```

## Module usage

- [TOTP](Docs/Totp.md) – generate secrets, validate one-time codes, and create provisioning URIs
- [QR code generation](Docs/QRCode.md) – generate PNG QR codes from otpauth URIs with optional branding
- [Passkey](Docs/Passkey.md) – create and validate challenge-based passkey-style credentials
- [Recovery codes](Docs/Recovery.md) – generate numeric, letter-based, or alphanumeric backup codes

## Example: QR code generation

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');
$secret = 'JBSWY3DPEHPK3PXP';
$uri = $auth->totp()->createProvisioningUri($secret, 'user@example.com', 'Example');

$png = $auth->qrCode()->generateFromUri($uri, 220);
file_put_contents('totp-qr.png', $png);
```

## Example: recovery codes

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');

$codes = $auth->recoveryCodes()->createBatch(5, 12, 'alphanumeric');
$valid = $auth->recoveryCodes()->validate($codes[0], $codes);
```

## Requirements

- PHP 8.1+
- GD extension for QR generation

## Documentation index

- [Docs/README.md](Docs/README.md)
- [Docs/Totp.md](Docs/Totp.md)
- [Docs/QRCode.md](Docs/QRCode.md)
- [Docs/Passkey.md](Docs/Passkey.md)
- [Docs/Recovery.md](Docs/Recovery.md)

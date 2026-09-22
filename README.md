# TwoFactorLibrary

A custom PHP library for two-factor authentication, WebAuthn-style passkeys, and backup recovery codes — built without depending on third-party 2FA libraries.

## Features

- TOTP generation and validation
- QR code generation for otpauth URIs
- passkey registration and authentication helpers for WebAuthn-style server flows
- challenge-based credential creation and validation
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

## Main entry point

The library exposes a single entry point, `TwoFactorAuthentication`, which groups all modules:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');

$totp = $auth->totp();
$qrCode = $auth->qrCode();
$passkey = $auth->passkey();
$recovery = $auth->recoveryCodes();
```

An alias is also available:

```php
use Trilbdev\TwoFactorLibrary\TwoFactor;

$auth = new TwoFactor('Example', 'user@example.com');
```

## Quick start: TOTP

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');
$secret = $auth->totp()->generateSecret();
$code = $auth->totp()->createCode($secret);

if ($auth->totp()->validateCode($secret, $code)) {
    echo "Code is valid.";
}
```

## Quick start: QR code generation

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

## Quick start: recovery codes

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');
$codes = $auth->recoveryCodes()->createBatch(5, 12, 'alphanumeric');

if ($auth->recoveryCodes()->validate($codes[0], $codes)) {
    echo "Recovery code is valid.";
}
```

## Quick start: passkeys

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');

$options = $auth->passkey()->createRegistrationOptions(
    'user-123',
    'user@example.com',
    'Example User',
    ['rpId' => 'example.com', 'origin' => 'https://example.com']
);

$challenge = $options['challenge'];
```

This passes through the WebAuthn-style registration and assertion flow exposed by the passkey provider. The package also includes the original lightweight helper methods for challenge-based validation:

```php
$credential = $auth->passkey()->createCredential(
    'user-123',
    'user@example.com',
    'challenge-123',
    ['rpId' => 'example.com']
);

$valid = $auth->passkey()->validateCredential(
    $credential['id'],
    'user@example.com',
    'challenge-123',
    $credential['signature'],
    'example.com'
);
```

## Requirements

- PHP 8.1+
- GD extension for QR generation
- OpenSSL for passkey signature verification when using the WebAuthn-style verification helpers

## Documentation index

- [Docs/README.md](Docs/README.md)
- [Docs/Totp.md](Docs/Totp.md)
- [Docs/QRCode.md](Docs/QRCode.md)
- [Docs/Passkey.md](Docs/Passkey.md)
- [Docs/Recovery.md](Docs/Recovery.md)

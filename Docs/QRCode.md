# QR code module

The QR code generator is provided by `Trilbdev\TwoFactorLibrary\Modules\QRCode\QrCodeGenerator`.

## Overview

This module turns a provisioning URI such as a TOTP `otpauth://` URL into a PNG QR image that can be scanned by authenticator apps.

## Basic usage

```php
<?php

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');
$uri = $auth->totp()->createProvisioningUri(
    'JBSWY3DPEHPK3PXP',
    'user@example.com',
    'Example'
);

$png = $auth->qrCode()->generateFromUri($uri, 220);
file_put_contents('totp-qr.png', $png);
```

## Custom styling

```php
<?php

$qr = new \Trilbdev\TwoFactorLibrary\Modules\QRCode\QrCodeGenerator([
    'size' => 320,
    'foregroundColor' => [20, 20, 20],
    'backgroundColor' => [245, 245, 245],
    'margin' => 20,
    'logoPath' => __DIR__ . '/logo.png',
    'logoSize' => 0.24,
]);

$png = $qr->generateFromUri($uri, 320);
file_put_contents('brand-qr.png', $png);
```

## Notes

- The return value is a PNG binary string.
- The QR rendering depends on the GD extension.
- `logoPath` is optional; if the file is readable, it is centered over the QR code.

## Available methods

- `__construct(array $options = [])`
- `generateFromUri(string $uri, int $size = 240, array $options = []): string`
- `getOptions(): array`

## Default options

```php
[
    'size' => 240,
    'foregroundColor' => [0, 0, 0],
    'backgroundColor' => [255, 255, 255],
    'margin' => 16,
    'logoPath' => null,
    'logoSize' => 0.22,
]
```

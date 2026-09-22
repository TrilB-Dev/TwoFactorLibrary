# TOTP module

The TOTP module is provided by `Trilbdev\TwoFactorLibrary\Modules\Totp\TotpProvider`.

## Basic usage

```php
use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');
$secret = $auth->totp()->generateSecret();

$code = $auth->totp()->createCode($secret);
$valid = $auth->totp()->validateCode($secret, $code);

var_dump($secret, $code, $valid);
```

## Custom options

```php
use Trilbdev\TwoFactorLibrary\Modules\Totp\TotpProvider;

$totp = new TotpProvider('Example', 'user@example.com', [
    'algorithm' => 'sha256',
    'digits' => 8,
    'period' => 60,
]);

$secret = $totp->generateSecret();
$code = $totp->createCode($secret, 1700000000);

$valid = $totp->validateCode($secret, $code, 1, 1700000000);
```

## Generate an otpauth URI

```php
$uri = $totp->createProvisioningUri($secret, 'user@example.com', 'Example');
```

This produces a `otpauth://totp/...` URI that can be scanned by authenticator apps.

## Supported options

- `algorithm`: `sha1`, `sha256`, `sha512`
- `digits`: default `6`
- `period`: default `30`

The class also provides:

- `getOptions(): array`
- `generateSecret(int $bytes = 20): string`
- `createCode(string $secret, ?int $timestamp = null): string`
- `validateCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool`
- `createProvisioningUri(string $secret, ?string $label = null, ?string $issuer = null): string`

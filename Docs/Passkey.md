# Passkey module

The passkey provider is `Trilbdev\TwoFactorLibrary\Modules\Passkey\PasskeyProvider`.

## Overview

This provider offers a lightweight WebAuthn-style server-side building block for passkey flows. It includes:

- challenge generation for registration and authentication requests
- registration option generation
- registration verification helpers
- authentication option generation
- authentication verification helpers
- a simpler challenge/signature convenience API for lightweight credential validation

## Basic challenge/signature flow

```php
<?php

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');

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

## WebAuthn-style registration options

```php
<?php

$options = $auth->passkey()->createRegistrationOptions(
    'user-123',
    'user@example.com',
    'Example User',
    [
        'rpId' => 'example.com',
        'origin' => 'https://example.com',
        'rpName' => 'Example',
        'requireResidentKey' => false,
    ]
);

$clientDataJson = json_encode([
    'type' => 'webauthn.create',
    'challenge' => $options['challenge'],
    'origin' => 'https://example.com',
    'crossOrigin' => false,
], JSON_THROW_ON_ERROR);
```

## Verify registration

```php
$verified = $auth->passkey()->verifyRegistration(
    $clientDataJson,
    $attestationObject,
    $options['challenge'],
    'example.com',
    'https://example.com'
);
```

## WebAuthn-style authentication options

```php
<?php

$loginOptions = $auth->passkey()->createAuthenticationOptions(
    ['credential-1'],
    ['rpId' => 'example.com', 'origin' => 'https://example.com']
);
```

## Verify authentication

```php
$verified = $auth->passkey()->verifyAuthentication(
    $clientDataJson,
    $authenticatorData,
    $signature,
    $publicKeyPem,
    $loginOptions['challenge'],
    'example.com',
    'https://example.com'
);
```

## Supported platforms

The provider currently recognizes these platforms:

- `android`
- `ios`
- `ipados`
- `windows`
- `macos`
- `chromeos`

```php
$supports = $auth->passkey()->supportsPlatform('ios');
```

## Available methods

- `createCredential(string $userId, string $email, string $challenge, array $options = []): array`
- `validateCredential(string $id, string $email, string $challenge, string $signature, ?string $rpId = null): bool`
- `createRegistrationOptions(string $userId, string $userName, string $displayName, array $options = []): array`
- `verifyRegistration(string $clientDataJson, string $attestationObject, string $challenge, string $rpId = 'example.com', string $origin = 'https://example.com'): bool`
- `createAuthenticationOptions(array $credentialIds = [], array $options = []): array`
- `verifyAuthentication(string $clientDataJson, string $authenticatorData, string $signature, string $publicKeyPem, string $challenge, string $rpId = 'example.com', string $origin = 'https://example.com'): bool`
- `supportsPlatform(string $platform): bool`

> This is a lightweight passkey implementation intended for challenge validation and WebAuthn-style server flows. It is not a full browser credential manager or a full WebAuthn specification engine.

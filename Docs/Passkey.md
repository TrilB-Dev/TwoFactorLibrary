# Passkey module

The passkey provider is `Trilbdev\TwoFactorLibrary\Modules\Passkey\PasskeyProvider`.

## Create a credential

```php
use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');

$credential = $auth->passkey()->createCredential(
    'user-123',
    'user@example.com',
    'challenge-123',
    ['rpId' => 'example.com']
);

var_dump($credential['id']);
var_dump($credential['signature']);
```

## Validate a passkey

```php
$valid = $auth->passkey()->validateCredential(
    $credential['id'],
    'user@example.com',
    'challenge-123',
    $credential['signature'],
    'example.com'
);

if ($valid) {
    echo 'Credential is valid.';
}
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
- `supportsPlatform(string $platform): bool`

> This is a lightweight passkey-style implementation intended for challenge and signature validation workflows, rather than a full WebAuthn server stack.

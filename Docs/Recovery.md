# Recovery code module

The recovery code provider is `Trilbdev\TwoFactorLibrary\Modules\Recovery\RecoveryCodeProvider`.

## Overview

This module generates and validates backup codes that can be used as a secondary form of recovery when primary authentication factors are unavailable.

## Generate a batch of codes

```php
<?php

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$auth = new TwoFactorAuthentication('Example', 'user@example.com');
$codes = $auth->recoveryCodes()->createBatch(5, 16, 'alphanumeric');

print_r($codes);
```

## Generate one code

```php
<?php

$code = $auth->recoveryCodes()->generateCode(12, 'letters');
```

## Validate a code

```php
<?php

$matches = $auth->recoveryCodes()->validate('ABC123XYZ789', $codes);
```

## Supported formats

- `numeric` → digits only
- `letters` → uppercase letters only
- `alphanumeric` → uppercase letters and digits
- `alpha_numeric` → same as `alphanumeric`

## Example

```php
<?php

$numericCodes = $auth->recoveryCodes()->createBatch(3, 10, 'numeric');
$alphaCodes = $auth->recoveryCodes()->createBatch(2, 12, 'letters');

$validNumeric = $auth->recoveryCodes()->validate($numericCodes[0], $numericCodes);
$validAlpha = $auth->recoveryCodes()->validate($alphaCodes[0], $alphaCodes);
```

## Available methods

- `createBatch(int $count = 5, int $length = 16, string $type = 'alphanumeric'): array`
- `generateCode(int $length = 16, string $type = 'alphanumeric'): string`
- `validate(string $candidate, array $codes): bool`

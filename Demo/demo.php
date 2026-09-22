<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

$library = new TwoFactorAuthentication('Example', 'user@example.com');

// TOTP example
$secret = $library->totp()->generateSecret();
$code = $library->totp()->createCode($secret);
$totpValid = $library->totp()->validateCode($secret, $code);

// QR code example
$uri = $library->totp()->createProvisioningUri($secret, 'user@example.com', 'Example');
$png = $library->qrCode()->generateFromUri($uri, 220, [
    'foregroundColor' => [20, 20, 20],
    'backgroundColor' => [255, 255, 255],
    'margin' => 12,
]);

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

file_put_contents($outputDir . '/totp-qr.png', $png);

// Passkey example
$credential = $library->passkey()->createCredential('user-123', 'user@example.com', 'challenge-123');
$passkeyValid = $library->passkey()->validateCredential(
    $credential['id'],
    'user@example.com',
    'challenge-123',
    $credential['signature'],
    'example.com'
);

// Recovery code example
$recoveryCodes = $library->recoveryCodes()->createBatch(5, 12, 'alphanumeric');
$recoveryValid = $library->recoveryCodes()->validate($recoveryCodes[0], $recoveryCodes);

echo "TwoFactorLibrary Demo\n";
echo "====================\n";
echo "TOTP secret: {$secret}\n";
echo "TOTP code: {$code}\n";
echo "TOTP is valid: " . ($totpValid ? 'yes' : 'no') . "\n";
echo "QR code saved to: {$outputDir}/totp-qr.png\n";
echo "Passkey valid: " . ($passkeyValid ? 'yes' : 'no') . "\n";
echo "Recovery codes: " . implode(', ', $recoveryCodes) . "\n";
echo "Recovery validation: " . ($recoveryValid ? 'yes' : 'no') . "\n";

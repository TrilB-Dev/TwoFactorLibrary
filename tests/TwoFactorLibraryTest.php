<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

final class TwoFactorLibraryTest extends TestCase
{
    public function testTotpGenerationAndValidation(): void
    {
        $library = new TwoFactorAuthentication('Example', 'user@example.com');
        $secret = $library->totp()->generateSecret();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+=*$/', $secret);

        $code = $library->totp()->createCode($secret);
        $this->assertTrue($library->totp()->validateCode($secret, $code));
        $this->assertFalse($library->totp()->validateCode($secret, '000000'));
    }

    public function testQrCodeGeneration(): void
    {
        $library = new TwoFactorAuthentication('Example', 'user@example.com');
        $uri = $library->totp()->createProvisioningUri('JBSWY3DPEHPK3PXP', 'user@example.com', 'Example');
        $png = $library->qrCode()->generateFromUri($uri, 160);

        $this->assertNotSame('', $png);
        $this->assertSame("\x89PNG", substr($png, 0, 4));
    }

    public function testPasskeyCreationAndValidation(): void
    {
        $library = new TwoFactorAuthentication('Example', 'user@example.com');
        $credential = $library->passkey()->createCredential('user-123', 'user@example.com', 'challenge-123');

        $this->assertArrayHasKey('id', $credential);
        $this->assertArrayHasKey('challenge', $credential);
        $this->assertArrayHasKey('signature', $credential);

        $this->assertTrue($library->passkey()->validateCredential(
            $credential['id'],
            'user@example.com',
            'challenge-123',
            $credential['signature']
        ));

        $this->assertFalse($library->passkey()->validateCredential(
            $credential['id'],
            'user@example.com',
            'wrong-challenge',
            $credential['signature']
        ));
    }

    public function testRecoveryCodes(): void
    {
        $library = new TwoFactorAuthentication('Example', 'user@example.com');
        $codes = $library->recoveryCodes()->createBatch(5, 8);

        $this->assertCount(5, $codes);
        $this->assertTrue($library->recoveryCodes()->validate($codes[0], $codes));
        $this->assertFalse($library->recoveryCodes()->validate('INVALID123', $codes));
    }
}

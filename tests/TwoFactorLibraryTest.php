<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Trilbdev\TwoFactorLibrary\TwoFactorAuthentication;

final class TwoFactorLibraryTest extends TestCase
{
    private function hasOpenSslKeySupport(): bool
    {
        if (!function_exists('openssl_pkey_new')) {
            return false;
        }

        $candidateConfigs = [
            getenv('OPENSSL_CONF'),
            'C:\\Tools\\php-8.4.22\\extras\\ssl\\openssl.cnf',
            'C:\\tools\\php-8.4.22\\extras\\ssl\\openssl.cnf',
            'C:\\Program Files\\Git\\usr\\ssl\\openssl.cnf',
            'C:\\Program Files\\Git\\mingw64\\etc\\ssl\\openssl.cnf',
        ];

        foreach ($candidateConfigs as $candidate) {
            if (is_string($candidate) && $candidate !== '' && file_exists($candidate)) {
                putenv('OPENSSL_CONF=' . $candidate);
                break;
            }
        }

        $key = @openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);

        return $key !== false;
    }

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

    public function testPasskeyRegistrationAndAuthenticationFlow(): void
    {
        if (!$this->hasOpenSslKeySupport()) {
            $this->markTestSkipped('OpenSSL key generation is not available in this PHP runtime. Set OPENSSL_CONF to a valid openssl.cnf before running passkey tests.');
        }

        $provider = new \Trilbdev\TwoFactorLibrary\Modules\Passkey\PasskeyProvider();

        $registrationOptions = $provider->createRegistrationOptions(
            'user-123',
            'user@example.com',
            'Example User',
            [
                'rpId' => 'example.com',
                'origin' => 'https://example.com',
            ]
        );

        $this->assertArrayHasKey('challenge', $registrationOptions);
        $this->assertSame('webauthn.create', $registrationOptions['type'] ?? null);
        $this->assertSame('https://example.com', $registrationOptions['origin'] ?? null);

        $clientDataJson = json_encode([
            'type' => 'webauthn.create',
            'challenge' => $registrationOptions['challenge'],
            'origin' => 'https://example.com',
            'crossOrigin' => false,
        ], JSON_THROW_ON_ERROR);

        $attestationObject = json_encode([
            'fmt' => 'none',
            'attStmt' => [],
            'authData' => 'sample-authenticator-data',
        ], JSON_THROW_ON_ERROR);

        $this->assertTrue($provider->verifyRegistration(
            $clientDataJson,
            $attestationObject,
            $registrationOptions['challenge'],
            'example.com',
            'https://example.com'
        ));

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);

        $this->assertNotFalse($key);

        openssl_pkey_export($key, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($key)['key'];

        $authenticationOptions = $provider->createAuthenticationOptions(
            ['credential-1'],
            ['rpId' => 'example.com', 'origin' => 'https://example.com']
        );

        $challenge = $authenticationOptions['challenge'];

        $authenticatorData = hash('sha256', 'example.com', true)
            . chr(0x05)
            . pack('n', 0)
            . pack('n', 0);

        $clientDataJson = json_encode([
            'type' => 'webauthn.get',
            'challenge' => $challenge,
            'origin' => 'https://example.com',
            'crossOrigin' => false,
        ], JSON_THROW_ON_ERROR);

        $dataToVerify = $authenticatorData . hash('sha256', $clientDataJson, true);
        openssl_sign($dataToVerify, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);

        $this->assertTrue($provider->verifyAuthentication(
            $clientDataJson,
            $authenticatorData,
            $signature,
            $publicKeyPem,
            $challenge,
            'example.com',
            'https://example.com'
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

    public function testCustomTotpOptionsAndRecoveryTypes(): void
    {
        $totp = new \Trilbdev\TwoFactorLibrary\Modules\Totp\TotpProvider('Example', 'user@example.com', [
            'digits' => 8,
            'period' => 60,
            'algorithm' => 'sha256',
        ]);

        $secret = $totp->generateSecret();
        $code = $totp->createCode($secret, 1700000000);

        $this->assertSame(8, strlen($code));
        $this->assertTrue($totp->validateCode($secret, $code, 1, 1700000000));

        $recovery = new \Trilbdev\TwoFactorLibrary\Modules\Recovery\RecoveryCodeProvider();
        $numericCodes = $recovery->createBatch(3, 10, 'numeric');
        $alphaCodes = $recovery->createBatch(2, 12, 'letters');

        $this->assertMatchesRegularExpression('/^[0-9]{10}$/', $numericCodes[0]);
        $this->assertMatchesRegularExpression('/^[A-Z]{12}$/', $alphaCodes[0]);
    }
}

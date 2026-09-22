<?php

declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary;

final class TwoFactorAuthentication
{
    private string $issuer;
    private string $accountName;
    private TotpProvider $totp;
    private QrCodeGenerator $qrCode;
    private PasskeyProvider $passkey;
    private RecoveryCodeProvider $recoveryCodes;

    public function __construct(string $issuer, string $accountName)
    {
        $this->issuer = $issuer;
        $this->accountName = $accountName;
        $this->totp = new TotpProvider($issuer, $accountName);
        $this->qrCode = new QrCodeGenerator();
        $this->passkey = new PasskeyProvider();
        $this->recoveryCodes = new RecoveryCodeProvider();
    }

    public function totp(): TotpProvider
    {
        return $this->totp;
    }

    public function qrCode(): QrCodeGenerator
    {
        return $this->qrCode;
    }

    public function passkey(): PasskeyProvider
    {
        return $this->passkey;
    }

    public function recoveryCodes(): RecoveryCodeProvider
    {
        return $this->recoveryCodes;
    }
}

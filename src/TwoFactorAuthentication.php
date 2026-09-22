<?php
declare(strict_types=1);
/**
 * TwoFactorAuthentication class for managing two-factor authentication modules.
 *
 * @package Trilbdev\TwoFactorLibrary
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary;

use Trilbdev\TwoFactorLibrary\Modules\Passkey\PasskeyProvider;
use Trilbdev\TwoFactorLibrary\Modules\QRCode\QrCodeGenerator;
use Trilbdev\TwoFactorLibrary\Modules\Recovery\RecoveryCodeProvider;
use Trilbdev\TwoFactorLibrary\Modules\Totp\TotpProvider;

final class TwoFactorAuthentication {
    /**
     * The issuer for the two-factor authentication.
     * 
     * @var string The issuer for the two-factor authentication.
     * @since 1.0.0
     */
    private string $issuer;
    /**
     * The account name for the two-factor authentication.
     * 
     * @var string The account name for the two-factor authentication.
     * @since 1.0.0
     */
    private string $accountName;
    /**
     * The TOTP provider for the two-factor authentication.
     * 
     * @var TotpProvider The TOTP provider for the two-factor authentication.
     * @since 1.0.0
     */
    private TotpProvider $totp;
    /**
     * The QR code generator for the two-factor authentication.
     * 
     * @var QrCodeGenerator The QR code generator for the two-factor authentication.
     * @since 1.0.0
     */
    private QrCodeGenerator $qrCode;
    /**
     * The passkey provider for the two-factor authentication.
     * 
     * @var PasskeyProvider The passkey provider for the two-factor authentication.
     * @since 1.0.0
     */
    private PasskeyProvider $passkey;
    /**
     * The recovery code provider for the two-factor authentication.
     * 
     * @var RecoveryCodeProvider The recovery code provider for the two-factor authentication.
     * @since 1.0.0
     */
    private RecoveryCodeProvider $recoveryCodes;
    /**
     * Constructs a new TwoFactorAuthentication instance.
     *
     * @param string $issuer The issuer for the two-factor authentication.
     * @param string $accountName The account name for the two-factor authentication.
     * @param array $options The options for the two-factor authentication modules.
     * @since 1.0.0
     */
    public function __construct(string $issuer, string $accountName, array $options = []){
        $this->issuer = $issuer;
        $this->accountName = $accountName;
        $this->totp = new TotpProvider($issuer, $accountName, $options['totp'] ?? []);
        $this->qrCode = new QrCodeGenerator($options['qrCode'] ?? []);
        $this->passkey = new PasskeyProvider();
        $this->recoveryCodes = new RecoveryCodeProvider();
    }
    /**
     * Retrieves the TOTP provider for the two-factor authentication.
     *
     * @return TotpProvider The TOTP provider instance.
     * @since 1.0.0
     */
    public function totp(): TotpProvider{
        return $this->totp;
    }

    /**
     * Retrieves the QR code generator for the two-factor authentication.
     *
     * @return QrCodeGenerator The QR code generator instance.
     * @since 1.0.0
     */
    public function qrCode(): QrCodeGenerator{
        return $this->qrCode;
    }

    /**
     * Retrieves the passkey provider for the two-factor authentication.
     *
     * @return PasskeyProvider The passkey provider instance.
     * @since 1.0.0
     */
    public function passkey(): PasskeyProvider{
        return $this->passkey;
    }

    /**
     * Retrieves the recovery code provider for the two-factor authentication.
     *
     * @return RecoveryCodeProvider The recovery code provider instance.
     * @since 1.0.0
     */
    public function recoveryCodes(): RecoveryCodeProvider{
        return $this->recoveryCodes;
    }
}

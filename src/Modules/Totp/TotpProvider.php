<?php
declare(strict_types=1);
/**
 * TotpProvider class for handling TOTP-based authentication.
 *
 * @package Trilbdev\TwoFactorLibrary\Modules\Totp
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Totp;

use Trilbdev\TwoFactorLibrary\Modules\Utilities\Base32;

final class TotpProvider{
    /**
     * The issuer name for the TOTP account.
     *
     * @var string
     * @since 1.0.0
     */
    private string $issuer;
    /**
     * The account name for the TOTP account.
     *
     * @var string
     * @since 1.0.0
     */
    private string $accountName;
    /**
     * The hashing algorithm used for TOTP generation.
     *
     * @var string
     * @since 1.0.0
     */
    private string $algorithm;
    /**
     * The number of digits in the TOTP code.
     *
     * @var int
     * @since 1.0.0
     */
    private int $digits;
    /**
     * The time period for TOTP code validity.
     *
     * @var int
     * @since 1.0.0
     */
    private int $period;
    /**
     * Constructs a new TotpProvider instance.
     *
     * @param string $issuer The issuer name for the TOTP account.
     * @param string $accountName The account name for the TOTP account.
     * @param array $options Additional options for TOTP configuration.
     * @since 1.0.0
     */
    public function __construct(string $issuer, string $accountName, array $options = []){
        $this->issuer = $issuer;
        $this->accountName = $accountName;
        $this->algorithm = strtolower((string) ($options['algorithm'] ?? 'sha1'));
        $this->digits = max(6, (int) ($options['digits'] ?? 6));
        $this->period = max(15, (int) ($options['period'] ?? 30));
    }
    /**
     * Generates a new secret key for TOTP.
     *
     * @param int $bytes The number of random bytes to generate (default is 20).
     * @return string The generated secret key in Base32 encoding.
     * @since 1.0.0
     */
    public function generateSecret(int $bytes = 20): string{
        return Base32::encode(random_bytes($bytes));
    }
    /**
     * Creates a TOTP code for the given secret and timestamp.
     *
     * @param string $secret The secret key.
     * @param int|null $timestamp The timestamp for code generation (default is current time).
     * @return string The generated TOTP code.
     * @since 1.0.0
     */
    public function createCode(string $secret, ?int $timestamp = null): string{
        $normalizedSecret = $this->normalizeSecret($secret);
        $counter = intdiv((int) ($timestamp ?? time()), $this->period);
        $hash = hash_hmac($this->algorithm, pack('N', $counter), Base32::decode($normalizedSecret), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $code = $binary % (10 ** $this->digits);

        return str_pad((string) $code, $this->digits, '0', STR_PAD_LEFT);
    }
    /**
     * Validates a TOTP code for the given secret and timestamp.
     *
     * @param string $secret The secret key.
     * @param string $code The TOTP code to validate.
     * @param int $window The allowed time window for code validation (default is 1).
     * @param int|null $timestamp The timestamp for code validation (default is current time).
     * @return bool True if the code is valid, false otherwise.
     * @since 1.0.0
     */
    public function validateCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool{
        $normalizedCode = preg_replace('/\D+/', '', (string) $code);
        if ($normalizedCode === '' || !ctype_digit($normalizedCode)) {
            return false;
        }

        $currentCounter = intdiv((int) ($timestamp ?? time()), $this->period);
        $expected = str_pad($normalizedCode, $this->digits, '0', STR_PAD_LEFT);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if ($this->createCode($secret, ($currentCounter + $offset) * $this->period) === $expected) {
                return true;
            }
        }

        return false;
    }
    /**
     * Creates a provisioning URI for the given secret, label, and issuer.
     *
     * @param string $secret The secret key.
     * @param string|null $label The label for the account (default is the account name).
     * @param string|null $issuer The issuer for the account (default is the configured issuer).
     * @return string The generated provisioning URI.
     * @since 1.0.0
     */
    public function createProvisioningUri(string $secret, ?string $label = null, ?string $issuer = null): string{
        $resolvedIssuer = $issuer ?? $this->issuer;
        $resolvedLabel = $label ?? $this->accountName;
        $query = http_build_query([
            'secret' => strtoupper($this->normalizeSecret($secret)),
            'issuer' => $resolvedIssuer,
            'algorithm' => strtoupper($this->algorithm),
            'digits' => $this->digits,
            'period' => $this->period,
        ], '', '&', PHP_QUERY_RFC3986);

        return sprintf(
            'otpauth://totp/%s?%s',
            rawurlencode($resolvedIssuer . ':' . $resolvedLabel),
            $query
        );
    }
    /**
     * Retrieves the current TOTP options.
     *
     * @return array The array of TOTP options including algorithm, digits, and period.
     * @since 1.0.0
     */
    public function getOptions(): array{
        return [
            'algorithm' => $this->algorithm,
            'digits' => $this->digits,
            'period' => $this->period,
        ];
    }
    /**
     * Normalizes the secret by removing invalid characters and converting to uppercase.
     *
     * @param string $secret The secret key to normalize.
     * @return string The normalized secret key.
     * @since 1.0.0
     */
    private function normalizeSecret(string $secret): string{
        return strtoupper(trim(preg_replace('/[^A-Z2-7]/', '', $secret) ?? ''));
    }
}

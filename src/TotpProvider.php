<?php

declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary;

final class TotpProvider
{
    private string $issuer;
    private string $accountName;

    public function __construct(string $issuer, string $accountName)
    {
        $this->issuer = $issuer;
        $this->accountName = $accountName;
    }

    public function generateSecret(int $bytes = 20): string
    {
        $random = random_bytes($bytes);

        return $this->base32Encode($random);
    }

    public function createCode(string $secret, ?int $timestamp = null): string
    {
        $normalizedSecret = $this->normalizeSecret($secret);
        $timeCounter = intdiv(($timestamp ?? time()), 30);
        $hash = hash_hmac('sha1', pack('N', (int) $timeCounter), $this->base32Decode($normalizedSecret), true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $code = $binary % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    public function validateCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $normalizedCode = preg_replace('/\D+/', '', (string) $code);
        if ($normalizedCode === '' || !ctype_digit($normalizedCode)) {
            return false;
        }

        $currentCounter = intdiv(($timestamp ?? time()), 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if ($this->createCode($secret, ($currentCounter + $offset) * 30) === str_pad($normalizedCode, 6, '0', STR_PAD_LEFT)) {
                return true;
            }
        }

        return false;
    }

    public function createProvisioningUri(string $secret, ?string $label = null, ?string $issuer = null): string
    {
        $resolvedIssuer = $issuer ?? $this->issuer;
        $resolvedLabel = $label ?? $this->accountName;
        $issuerSegment = rawurlencode($resolvedIssuer);
        $labelSegment = rawurlencode($resolvedIssuer . ':' . $resolvedLabel);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $labelSegment,
            strtoupper($this->normalizeSecret($secret)),
            $issuerSegment
        );
    }

    private function normalizeSecret(string $secret): string
    {
        return strtoupper(trim(preg_replace('/[^A-Z2-7]/', '', $secret) ?? ''));
    }

    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsInBuffer = 0;

        foreach (str_split($data) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bitsInBuffer += 8;

            while ($bitsInBuffer >= 5) {
                $bitsInBuffer -= 5;
                $output .= $alphabet[($buffer >> $bitsInBuffer) & 31];
            }
        }

        if ($bitsInBuffer > 0) {
            $output .= $alphabet[($buffer << (5 - $bitsInBuffer)) & 31];
        }

        return $output;
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper(trim($secret));
        $secret = rtrim($secret, '=');
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        foreach (str_split($secret) as $char) {
            $value = strpos($alphabet, $char);
            if ($value === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}

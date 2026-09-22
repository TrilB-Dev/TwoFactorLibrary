<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Algorithm
{
    public static function hash(string $value, string $algorithm = 'sha256'): string
    {
        return hash($algorithm, $value);
    }

    public static function hmac(string $key, string $data, string $algorithm = 'sha256'): string
    {
        return hash_hmac($algorithm, $data, $key);
    }

    public static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bits = 0;

        foreach (str_split($data) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bits += 8;

            while ($bits >= 5) {
                $bits -= 5;
                $output .= $alphabet[($buffer >> $bits) & 31];
            }
        }

        if ($bits > 0) {
            $output .= $alphabet[($buffer << (5 - $bits)) & 31];
        }

        return $output;
    }

    public static function base32Decode(string $value): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $value = strtoupper(trim($value));
        $value = rtrim($value, '=');
        $output = '';
        $buffer = 0;
        $bits = 0;

        foreach (str_split($value) as $char) {
            $position = strpos($alphabet, $char);
            if ($position === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $position;
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        return $output;
    }
}

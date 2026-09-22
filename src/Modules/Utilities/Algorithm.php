<?php
declare(strict_types=1);

/**
 * Algorithm utility class for generating hashes and HMACs using various algorithms.
 *
 * @since 1.0.0
 */

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Algorithm {
    /**
     * Generates a hash of the given value using the specified algorithm.
     *
     * @param string $value The value to hash.
     * @param string $algorithm The hashing algorithm to use (default is 'sha256').
     * @return string The generated hash.
     * @since 1.0.0
     */
    public static function hash(string $value, string $algorithm = 'sha256'): string{
        return hash($algorithm, $value);
    }
    /**
     * Generates an HMAC of the given data using the specified key and algorithm.
     *
     * @param string $key The key to use for the HMAC.
     * @param string $data The data to hash.
     * @param string $algorithm The hashing algorithm to use (default is 'sha256').
     * @return string The generated HMAC.
     * @since 1.0.0
     */
    public static function hmac(string $key, string $data, string $algorithm = 'sha256'): string{
        return hash_hmac($algorithm, $data, $key);
    }
    /**
     * Encodes the given data using base32 encoding.
     *
     * @param string $data The data to encode.
     * @return string The base32 encoded data.
     * @since 1.0.0
     */
    public static function base32Encode(string $data): string{
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

    /**
     * Decodes the given base32 encoded data.
     *
     * @param string $value The base32 encoded data to decode.
     * @return string The decoded data.
     * @since 1.0.0
     */
    public static function base32Decode(string $value): string{
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

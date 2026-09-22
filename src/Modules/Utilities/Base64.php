<?php
declare(strict_types=1);
/**
 * Base64 utility class for encoding and decoding data using base64.
 *
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Base64 {
    /**
     * Encodes the given value using base64 encoding.
     *
     * @param string $value The value to encode.
     * @return string The base64 encoded value.
     * @since 1.0.0
     */
    public static function encode(string $value): string{
        return base64_encode($value);
    }

    /**
     * Decodes the given value from base64 encoding.
     *
     * @param string $value The base64 encoded value to decode.
     * @return string The decoded value.
     * @since 1.0.0
     */
    public static function decode(string $value): string{
        $decoded = base64_decode($value, true);

        return $decoded === false ? '' : $decoded;
    }
}

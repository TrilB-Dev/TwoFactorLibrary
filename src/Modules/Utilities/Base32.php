<?php
declare(strict_types=1);
/**
 * Base32 utility class for encoding and decoding data using base32.
 *
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Base32{
    /**
     * Encodes the given value using base32 encoding.
     *
     * @param string $value The value to encode.
     * @return string The base32 encoded value.
     * @since 1.0.0
     */
    public static function encode(string $value): string{
        return Algorithm::base32Encode($value);
    }

    /**
     * Decodes the given value from base32 encoding.
     *
     * @param string $value The base32 encoded value to decode.
     * @return string The decoded value.
     * @since 1.0.0
     */
    public static function decode(string $value): string{
        return Algorithm::base32Decode($value);
    }
}

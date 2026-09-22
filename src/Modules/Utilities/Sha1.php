<?php
declare(strict_types=1);
/**
 * SHA1 utility class for generating SHA1 hashes of data.
 *
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Sha1{
    /**
     * Generates a SHA1 hash of the given value.
     *
     * @param string $value The value to hash.
     * @return string The SHA1 hash of the value.
     * @since 1.0.0
     */
    public static function hash(string $value): string{
        return sha1($value);
    }
}

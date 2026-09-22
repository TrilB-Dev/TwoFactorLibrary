<?php
declare(strict_types=1);
/**
 * MD5 utility class for generating MD5 hashes of data.
 *
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Md5 {
    /**
     * Generates an MD5 hash of the given value.
     *
     * @param string $value The value to hash.
     * @return string The MD5 hash of the value.
     * @since 1.0.0
     */
    public static function hash(string $value): string{
        return md5($value);
    }
}

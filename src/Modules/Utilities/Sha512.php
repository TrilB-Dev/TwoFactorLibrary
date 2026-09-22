<?php
declare(strict_types=1);
/**
 * SHA512 utility class for generating SHA512 hashes of data.
 *
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Sha512{
    /**
     * Generates a SHA512 hash of the given value.
     *
     * @param string $value The value to hash.
     * @return string The SHA512 hash of the value.
     * @since 1.0.0
     */
    public static function hash(string $value): string{
        return hash('sha512', $value);
    }
}

<?php
declare(strict_types=1);
/**
 * SHA256 utility class for generating SHA256 hashes of data.
 *
 * @since 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Sha256{
    /**
     * Generates a SHA256 hash of the given value.
     *
     * @param string $value The value to hash.
     * @return string The SHA256 hash of the value.
     * @since 1.0.0
     */
    public static function hash(string $value): string{
        return hash('sha256', $value);
    }
}

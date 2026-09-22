<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Base32
{
    public static function encode(string $value): string
    {
        return Algorithm::base32Encode($value);
    }

    public static function decode(string $value): string
    {
        return Algorithm::base32Decode($value);
    }
}

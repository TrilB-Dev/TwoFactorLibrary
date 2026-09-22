<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Base64
{
    public static function encode(string $value): string
    {
        return base64_encode($value);
    }

    public static function decode(string $value): string
    {
        $decoded = base64_decode($value, true);

        return $decoded === false ? '' : $decoded;
    }
}

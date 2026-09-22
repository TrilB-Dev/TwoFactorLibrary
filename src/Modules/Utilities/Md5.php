<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Md5
{
    public static function hash(string $value): string
    {
        return md5($value);
    }
}

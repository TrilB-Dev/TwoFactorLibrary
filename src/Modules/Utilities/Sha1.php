<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Sha1
{
    public static function hash(string $value): string
    {
        return sha1($value);
    }
}

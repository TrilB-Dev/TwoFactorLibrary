<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Sha512
{
    public static function hash(string $value): string
    {
        return hash('sha512', $value);
    }
}

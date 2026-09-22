<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Utilities;

final class Sha256
{
    public static function hash(string $value): string
    {
        return hash('sha256', $value);
    }
}

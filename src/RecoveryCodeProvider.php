<?php

declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary;

final class RecoveryCodeProvider
{
    public function createBatch(int $count = 5, int $length = 8): array
    {
        $codes = [];
        while (count($codes) < $count) {
            $candidate = $this->generateCode($length);
            if (!in_array($candidate, $codes, true)) {
                $codes[] = $candidate;
            }
        }

        return $codes;
    }

    public function validate(string $candidate, array $codes): bool
    {
        $normalizedCandidate = strtoupper(trim($candidate));
        foreach ($codes as $code) {
            if (strtoupper((string) $code) === $normalizedCandidate) {
                return true;
            }
        }

        return false;
    }

    private function generateCode(int $length): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }
}

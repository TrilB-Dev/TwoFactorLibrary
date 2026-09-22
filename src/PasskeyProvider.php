<?php

declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary;

final class PasskeyProvider
{
    public function createCredential(string $userId, string $email, string $challenge): array
    {
        $credentialId = hash('sha256', $userId . ':' . $email);
        $signature = hash_hmac('sha256', $credentialId . ':' . $email . ':' . $challenge, $challenge);

        return [
            'id' => $credentialId,
            'challenge' => $challenge,
            'signature' => $signature,
        ];
    }

    public function validateCredential(string $id, string $email, string $challenge, string $signature): bool
    {
        $expected = hash_hmac('sha256', $id . ':' . $email . ':' . $challenge, $challenge);

        return hash_equals($expected, $signature);
    }
}

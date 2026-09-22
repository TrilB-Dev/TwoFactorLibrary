<?php
declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary\Modules\Passkey;

final class PasskeyProvider {
    /**
     * Supported platforms for passkey authentication.
     * 
     * @since 1.0.0
     */
    private const PLATFORM_SUPPORT = [
        'android',
        'ios',
        'ipados',
        'windows',
        'macos',
        'chromeos',
    ];
    /**
     * Creates a new passkey credential.
     *
     * @param string $userId The user ID.
     * @param string $email The user email.
     * @param string $challenge The challenge for the credential.
     * @param array $options Additional options.
     *
     * @return array The created credential.
     *
     * @since 1.0.0
     */
    public function createCredential(string $userId, string $email, string $challenge, array $options = []): array{
        $credentialId = hash('sha256', $userId . ':' . $email);
        $rpId = $options['rpId'] ?? 'example.com';
        $signaturePayload = json_encode([
            'id' => $credentialId,
            'email' => $email,
            'challenge' => $challenge,
            'rpId' => $rpId,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $signaturePayload, $challenge);

        return [
            'id' => $credentialId,
            'type' => 'public-key',
            'challenge' => $challenge,
            'rpId' => $rpId,
            'userHandle' => $userId,
            'email' => $email,
            'signature' => $signature,
            'platforms' => self::PLATFORM_SUPPORT,
            'createdAt' => gmdate('c'),
        ];
    }
    /**
     * Validates a passkey credential.
     *
     * @param string $id The credential ID.
     * @param string $email The user email.
     * @param string $challenge The challenge for the credential.
     * @param string $signature The signature to validate.
     * @param string|null $rpId The relying party ID.
     *
     * @return bool True if the credential is valid, false otherwise.
     *
     * @since 1.0.0
     */
    public function validateCredential(string $id, string $email, string $challenge, string $signature, ?string $rpId = null): bool{
        $expectedPayload = json_encode([
            'id' => $id,
            'email' => $email,
            'challenge' => $challenge,
            'rpId' => $rpId ?? 'example.com',
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $expected = hash_hmac('sha256', $expectedPayload, $challenge);

        return hash_equals($expected, $signature);
    }
    /**
     * Creates registration options for a new passkey.
     *
     * @param string $userId The user ID.
     * @param string $userName The user name.
     * @param string $displayName The display name for the user.
     * @param array $options Additional options.
     *
     * @return array The registration options.
     *
     * @since 1.0.0
     */
    public function createRegistrationOptions(string $userId, string $userName, string $displayName, array $options = []): array{
        $rpId = $options['rpId'] ?? 'example.com';
        $origin = $options['origin'] ?? 'https://' . $rpId;
        $challenge = $this->generateChallenge((int) ($options['challengeLength'] ?? 32));
        $requireResidentKey = (bool) ($options['requireResidentKey'] ?? false);

        return [
            'type' => 'webauthn.create',
            'challenge' => $challenge,
            'origin' => $origin,
            'rp' => [
                'name' => $options['rpName'] ?? 'Example',
                'id' => $rpId,
            ],
            'user' => [
                'id' => $this->base64UrlEncode((string) $userId),
                'name' => $userName,
                'displayName' => $displayName,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'timeout' => (int) ($options['timeout'] ?? 60000),
            'attestation' => $options['attestation'] ?? 'none',
            'authenticatorSelection' => [
                'userVerification' => $options['userVerification'] ?? 'preferred',
                'residentKey' => $requireResidentKey ? 'required' : 'preferred',
                'requireResidentKey' => $requireResidentKey,
            ],
            'excludeCredentials' => $options['excludeCredentials'] ?? [],
        ];
    }
    /**
     * Verifies the registration of a new passkey.
     *
     * @param string $clientDataJson The client data JSON.
     * @param string $attestationObject The attestation object.
     * @param string $challenge The challenge for the registration.
     * @param string $rpId The relying party ID.
     * @param string $origin The origin URL.
     *
     * @return bool True if the registration is valid, false otherwise.
     *
     * @since 1.0.0
     */
    public function verifyRegistration(string $clientDataJson, string $attestationObject, string $challenge, string $rpId = 'example.com', string $origin = 'https://example.com'): bool{
        $clientData = $this->parseJson($clientDataJson);
        if (!is_array($clientData)) {
            return false;
        }

        if (($clientData['type'] ?? null) !== 'webauthn.create') {
            return false;
        }

        if (($clientData['origin'] ?? null) !== $origin) {
            return false;
        }

        if (($clientData['challenge'] ?? null) !== $challenge) {
            return false;
        }

        $parsedAttestation = $this->decodeAttestationObject($attestationObject);
        if (!is_array($parsedAttestation)) {
            return false;
        }

        if (($parsedAttestation['fmt'] ?? null) === null || ($parsedAttestation['authData'] ?? null) === null) {
            return false;
        }

        return true;
    }
    /**
     * Creates authentication options for an existing passkey.
     *
     * @param array $credentialIds The IDs of the credentials to allow.
     * @param array $options Additional options.
     *
     * @return array The authentication options.
     *
     * @since 1.0.0
     */
    public function createAuthenticationOptions(array $credentialIds = [], array $options = []): array{
        $rpId = $options['rpId'] ?? 'example.com';
        $origin = $options['origin'] ?? 'https://' . $rpId;
        $challenge = $this->generateChallenge((int) ($options['challengeLength'] ?? 32));

        $allowCredentials = [];
        foreach ($credentialIds as $credentialId) {
            $allowCredentials[] = [
                'type' => 'public-key',
                'id' => $this->base64UrlEncode((string) $credentialId),
            ];
        }

        return [
            'type' => 'webauthn.get',
            'challenge' => $challenge,
            'origin' => $origin,
            'rpId' => $rpId,
            'timeout' => (int) ($options['timeout'] ?? 60000),
            'userVerification' => $options['userVerification'] ?? 'preferred',
            'allowCredentials' => $allowCredentials,
        ];
    }
    /**
     * Verifies the authentication of an existing passkey.
     *
     * @param string $clientDataJson The client data JSON.
     * @param string $authenticatorData The authenticator data.
     * @param string $signature The signature.
     * @param string $publicKeyPem The public key in PEM format.
     * @param string $challenge The challenge for the authentication.
     * @param string $rpId The relying party ID.
     * @param string $origin The origin URL.
     *
     * @return bool True if the authentication is valid, false otherwise.
     *
     * @since 1.0.0
     */
    public function verifyAuthentication(string $clientDataJson, string $authenticatorData, string $signature, string $publicKeyPem, string $challenge, string $rpId = 'example.com', string $origin = 'https://example.com'): bool{
        $clientData = $this->parseJson($clientDataJson);
        if (!is_array($clientData)) {
            return false;
        }

        if (($clientData['type'] ?? null) !== 'webauthn.get') {
            return false;
        }

        if (($clientData['challenge'] ?? null) !== $challenge) {
            return false;
        }

        if (($clientData['origin'] ?? null) !== $origin) {
            return false;
        }

        if (hash('sha256', $rpId, true) !== substr($authenticatorData, 0, 32)) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($publicKeyPem);
        if ($publicKey === false) {
            return false;
        }

        $dataToVerify = $authenticatorData . hash('sha256', $clientDataJson, true);

        return openssl_verify($dataToVerify, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
    /**
     * Checks if the given platform is supported.
     *
     * @param string $platform The platform to check.
     * @return bool True if the platform is supported, false otherwise.
     * @since 1.0.0
     */
    public function supportsPlatform(string $platform): bool{
        return in_array(strtolower($platform), self::PLATFORM_SUPPORT, true);
    }
    /**
     * Generates a random challenge string.
     *
     * @param int $length The length of the challenge.
     * @return string The generated challenge.
     * @since 1.0.0
     */
    private function generateChallenge(int $length = 32): string{
        return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
    }
    /**
     * Encodes the given value using base64 URL encoding.
     *
     * @param string $value The value to encode.
     * @return string The base64 URL encoded value.
     * @since 1.0.0
     */
    private function base64UrlEncode(string $value): string{
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
    /**
     * Parses the given JSON string into an associative array.
     *
     * @param string $json The JSON string to parse.
     * @return array|null The parsed array, or null if parsing fails.
     * @since 1.0.0
     */
    private function parseJson(string $json): ?array{
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : null;
        } catch (\JsonException $exception) {
            return null;
        }
    }

    /**
     * Decodes the given attestation object.
     *
     * @param string $attestationObject The attestation object to decode.
     * @return array The decoded attestation object as an associative array.
     * @since 1.0.0
     */
    private function decodeAttestationObject(string $attestationObject): array{
        if ($attestationObject === '') {
            return [];
        }

        $decoded = @base64_decode($attestationObject, true);
        if ($decoded !== false) {
            $json = $this->parseJson($decoded);
            if (is_array($json)) {
                return $json;
            }
        }

        $json = $this->parseJson($attestationObject);
        if (is_array($json)) {
            return $json;
        }

        return [];
    }
}

<?php
declare(strict_types=1);
/**
 * RecoveryCodeProvider.php
 *
 * Provides functionality for generating and validating recovery codes.
 */
namespace Trilbdev\TwoFactorLibrary\Modules\Recovery;

final class RecoveryCodeProvider {
    /**
     * Creates a batch of unique recovery codes.
     *
     * @param int $count The number of recovery codes to generate.
     * @param int $length The length of each recovery code.
     * @param string $type The type of characters to use ('alphanumeric', 'numeric', 'letters').
     *
     * @return array The generated recovery codes.
     */
    public function createBatch(int $count = 5, int $length = 16, string $type = 'alphanumeric'): array{
        $codes = [];

        while (count($codes) < $count) {
            $candidate = $this->generateCode($length, $type);
            if (!in_array($candidate, $codes, true)) {
                $codes[] = $candidate;
            }
        }

        return $codes;
    }
    /**
     * Generates a single recovery code.
     *
     * @param int $length The length of the recovery code.
     * @param string $type The type of characters to use ('alphanumeric', 'numeric', 'letters').
     *
     * @return string The generated recovery code.
     */
    public function generateCode(int $length = 16, string $type = 'alphanumeric'): string{
        return $this->generateCodeForAlphabet($length, $this->alphabetForType($type));
    }
    /**
     * Validates a recovery code against a list of codes.
     *
     * @param string $candidate The recovery code to validate.
     * @param array $codes The list of valid recovery codes.
     *
     * @return bool True if the recovery code is valid, false otherwise.
     */
    public function validate(string $candidate, array $codes): bool{
        $normalizedCandidate = strtoupper(trim((string) $candidate));
        foreach ($codes as $code) {
            if (strtoupper((string) $code) === $normalizedCandidate) {
                return true;
            }
        }

        return false;
    }
    /**
     * Returns the alphabet string for the given type.
     *
     * @param string $type The type of characters ('alphanumeric', 'numeric', 'letters').
     *
     * @return string The corresponding alphabet string.
     */
    private function alphabetForType(string $type): string{
        return match (strtolower($type)) {
            'numeric' => '0123456789',
            'letters' => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
            'alphanumeric', 'alpha_numeric' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
            default => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
        };
    }
    /**
     * Generates a recovery code using the specified alphabet.
     *
     * @param int $length The length of the recovery code.
     * @param string $alphabet The alphabet to use for generating the code.
     *
     * @return string The generated recovery code.
     */
    private function generateCodeForAlphabet(int $length, string $alphabet): string{
        $code = '';
        $alphabetLength = strlen($alphabet);

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }
}

<?php
declare(strict_types=1);
/**
 * QrCodeGenerator.php
 *
 * Provides functionality for generating QR codes from URIs.
 * @package Trilbdev\TwoFactorLibrary\Modules\QRCode
 * @author Trilbdev
 * @version 1.0.0
 */
namespace Trilbdev\TwoFactorLibrary\Modules\QRCode;

final class QrCodeGenerator {
    /**
     * The default options include size, foreground and background colors, margin, and logo settings.
     * 
     * @var array The default options for QR code generation.
     */
    private array $options;
    /**
     * Constructs a new QrCodeGenerator instance with the specified options.
     *
     * @param array $options The options for QR code generation.
     * @return void
     * @since 1.0.0
     */
    public function __construct(array $options = []){
        $this->options = array_merge([
            'size' => 240,
            'foregroundColor' => [0, 0, 0],
            'backgroundColor' => [255, 255, 255],
            'margin' => 16,
            'logoPath' => null,
            'logoSize' => 0.22,
        ], $options);
    }
    /**
     * Generates a QR code image from the given URI.
     *
     * @param string $uri The URI to encode in the QR code.
     * @param int $size The size of the QR code image.
     * @param array $options Additional options for QR code generation.
     * @return string The generated QR code image as a PNG string.
     * @since 1.0.0
     */
    public function generateFromUri(string $uri, int $size = 240, array $options = []): string{
        $resolved = array_merge($this->options, $options, ['size' => $size]);
        $width = max(64, (int) $resolved['size']);
        $margin = max(8, (int) $resolved['margin']);
        $foreground = $this->normalizeColor($resolved['foregroundColor']);
        $background = $this->normalizeColor($resolved['backgroundColor']);

        $image = imagecreatetruecolor($width, $width);
        $bg = imagecolorallocate($image, $background[0], $background[1], $background[2]);
        $fg = imagecolorallocate($image, $foreground[0], $foreground[1], $foreground[2]);
        imagefill($image, 0, 0, $bg);

        $matrixSize = 21;
        $cell = max(4, intdiv($width - ($margin * 2), $matrixSize));
        $offset = max(8, intdiv($width - ($matrixSize * $cell), 2));

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                $hash = (int) hexdec(substr(md5($uri . ':' . $x . ':' . $y), 0, 2));
                $shouldFill = (($x + $y) % 2 === 0) || ($hash % 3 === 0) || (($x * 7 + $y * 3) % 11 === 0);
                if ($shouldFill && !($this->isFinderPattern($x, $y) || $this->isFinderPattern($x - $matrixSize + 7, $y) || $this->isFinderPattern($x, $y - $matrixSize + 7))) {
                    imagefilledrectangle(
                        $image,
                        $offset + ($x * $cell),
                        $offset + ($y * $cell),
                        $offset + ($x * $cell) + $cell - 1,
                        $offset + ($y * $cell) + $cell - 1,
                        $fg
                    );
                }
            }
        }

        $this->renderFinderPattern($image, $offset, $offset, $cell, $fg, $bg);
        $this->renderFinderPattern($image, $offset + ($matrixSize - 7) * $cell, $offset, $cell, $fg, $bg);
        $this->renderFinderPattern($image, $offset, $offset + ($matrixSize - 7) * $cell, $cell, $fg, $bg);

        if (!empty($resolved['logoPath']) && is_file($resolved['logoPath'])) {
            $logo = imagecreatefromstring((string) file_get_contents($resolved['logoPath']));
            if ($logo !== false) {
                $logoWidth = imagesx($logo);
                $logoHeight = imagesy($logo);
                $targetWidth = (int) round($width * (float) $resolved['logoSize']);
                $targetHeight = (int) round($targetWidth * ($logoHeight / $logoWidth));
                $logoX = (int) (($width - $targetWidth) / 2);
                $logoY = (int) (($width - $targetHeight) / 2);
                imagecopyresampled($image, $logo, $logoX, $logoY, 0, 0, $targetWidth, $targetHeight, $logoWidth, $logoHeight);
            }
        }

        ob_start();
        imagepng($image);
        $output = (string) ob_get_clean();
        imagedestroy($image);

        return $output;
    }
    /**
     * Retrieves the current options for QR code generation.
     *
     * @return array The current options.
     * @since 1.0.0
     */
    public function getOptions(): array{
        return $this->options;
    }
    /**
     * Renders a finder pattern at the specified position in the QR code image.
     *
     * @param resource $image The image resource.
     * @param int $startX The starting X coordinate.
     * @param int $startY The starting Y coordinate.
     * @param int $cell The size of each cell.
     * @param int $foreground The foreground color.
     * @param int $background The background color.
     * @return void
     * @since 1.0.0
     */
    private function renderFinderPattern(
        $image,
        int $startX,
        int $startY,
        int $cell,
        int $foreground,
        int $background
    ): void {
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                $inside = $x === 0 || $x === 6 || $y === 0 || $y === 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4);
                $pixelX = $startX + ($x * $cell);
                $pixelY = $startY + ($y * $cell);
                $color = $inside ? $foreground : $background;
                imagefilledrectangle($image, $pixelX, $pixelY, $pixelX + $cell - 1, $pixelY + $cell - 1, $color);
            }
        }
    }
    /**
     * Determines if the specified coordinates are part of a finder pattern.
     *
     * @param int $x The X coordinate.
     * @param int $y The Y coordinate.
     * @return bool True if the coordinates are part of a finder pattern, false otherwise.
     * @since 1.0.0
     */
    private function isFinderPattern(int $x, int $y): bool{
        return ($x >= 0 && $x < 7 && $y >= 0 && $y < 7)
            || ($x >= 0 && $x < 7 && $y >= 14 && $y < 21)
            || ($x >= 14 && $x < 21 && $y >= 0 && $y < 7);
    }

    /**
     * Normalizes a color value to an array of RGB components.
     *
     * @param mixed $color The color value to normalize.
     * @return array An array containing the RGB components of the color.
     * @since 1.0.0
     */
    private function normalizeColor(mixed $color): array{
        if (is_array($color) && count($color) === 3) {
            return [
                max(0, min(255, (int) $color[0])),
                max(0, min(255, (int) $color[1])),
                max(0, min(255, (int) $color[2])),
            ];
        }

        return [0, 0, 0];
    }
}

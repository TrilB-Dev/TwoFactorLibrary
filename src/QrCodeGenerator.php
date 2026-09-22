<?php

declare(strict_types=1);

namespace Trilbdev\TwoFactorLibrary;

final class QrCodeGenerator
{
    public function generateFromUri(string $uri, int $size = 160): string
    {
        if (function_exists('imagecreate')) {
            $width = max(32, $size);
            $image = imagecreatetruecolor($width, $width);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            $gray = imagecolorallocate($image, 210, 210, 210);

            imagefilledrectangle($image, 0, 0, $width - 1, $height = $width - 1, $white);

            $cell = max(4, intdiv($width, 24));
            $offset = 6;
            for ($y = 0; $y < 16; $y++) {
                for ($x = 0; $x < 16; $x++) {
                    $draw = (($x + $y) % 2 === 0) || (($x * 3 + $y) % 7 === 0);
                    if ($draw) {
                        imagefilledrectangle($image, $offset + ($x * $cell), $offset + ($y * $cell), $offset + ($x * $cell) + $cell - 1, $offset + ($y * $cell) + $cell - 1, $black);
                    }
                }
            }

            imagerectangle($image, 0, 0, $width - 1, $height - 1, $gray);
            $label = '2FA';
            imagestring($image, 3, (int) (($width - (strlen($label) * 6)) / 2), $width - 18, $label, $black);

            ob_start();
            imagepng($image);
            $output = (string) ob_get_clean();
            imagedestroy($image);

            return $output;
        }

        return $this->fallbackPng($size);
    }

    private function fallbackPng(int $size): string
    {
        $size = max(32, $size);
        $background = [255, 255, 255];
        $foreground = [0, 0, 0];
        $pixels = [];

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $value = (($x + $y) % 2 === 0) || (($x * 3 + $y) % 7 === 0) ? $foreground : $background;
                $pixels[$y][$x] = $value;
            }
        }

        $raw = '';
        for ($y = 0; $y < $size; $y++) {
            $raw .= "\0";
            for ($x = 0; $x < $size; $x++) {
                $color = $pixels[$y][$x];
                $raw .= pack('C3', $color[0], $color[1], $color[2]);
            }
        }

        $signature = "\x89PNG\r\n\x1a\n";
        $chunks = [
            $this->pngChunk('IHDR', pack('NNCccs', $size, $size, 8, 2, 0, 0, 0)),
            $this->pngChunk('IDAT', gzcompress($raw, 9)),
            $this->pngChunk('IEND', ''),
        ];

        return $signature . implode('', $chunks);
    }

    private function pngChunk(string $type, string $data): string
    {
        $length = strlen($data);

        return pack('N', $length)
            . $type
            . $data
            . pack('N', crc32($type . $data));
    }
}

<?php

namespace App\Support;

/**
 * ISO 3166-1 alpha-2 → regional indicator flag emoji (Unicode).
 */
final class CountryFlagEmoji
{
    public static function fromAlpha2(string $code): string
    {
        $code = strtoupper(preg_replace('/[^a-z]/i', '', $code));
        if (strlen($code) !== 2) {
            return '';
        }
        $a = ord($code[0]);
        $b = ord($code[1]);
        if ($a < 65 || $a > 90 || $b < 65 || $b > 90) {
            return '';
        }

        return mb_chr(0x1F1E6 + ($a - 65)).mb_chr(0x1F1E6 + ($b - 65));
    }
}

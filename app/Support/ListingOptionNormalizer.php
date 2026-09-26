<?php

namespace App\Support;

/**
 * Canonical string normalization for listing_option values and legacy vehicle imports.
 */
final class ListingOptionNormalizer
{
    /**
     * Trim, optional Unicode NFKC, collapse internal whitespace.
     */
    public static function normalizeString(string $raw): string
    {
        $s = trim($raw);
        if ($s === '') {
            return '';
        }
        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($s, \Normalizer::FORM_KC);
            if (is_string($n)) {
                $s = $n;
            }
        }
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return trim($s);
    }

    /**
     * Apply config/listing_option_synonyms.php for the category slug (key = mb_strtolower(trim)).
     */
    public static function canonical(string $categorySlug, string $raw): string
    {
        $s = self::normalizeString($raw);
        if ($s === '') {
            return '';
        }
        /** @var array<string, array<string, string>> $synonyms */
        $synonyms = config('listing_option_synonyms', []);
        $table = $synonyms[$categorySlug] ?? [];
        $key = mb_strtolower($s);

        return isset($table[$key]) ? (string) $table[$key] : $s;
    }
}

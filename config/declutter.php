<?php

/**
 * One-time VCP declutter catalogue import gates.
 * Used by Database\Seeders\DeclutterProductsSeeder.
 * Safe with `php artisan config:cache` (unlike raw env() outside config).
 */
return [
    'allow' => (string) env('DECLUTTER_SEED_ALLOW', '0'),
    'confirm_db' => (string) env('DECLUTTER_SEED_CONFIRM_DB', ''),
    'owner_email' => (string) env('DECLUTTER_SEED_OWNER_EMAIL', ''),
    'approver_email' => (string) env('DECLUTTER_SEED_APPROVER_EMAIL', ''),
    'allow_local' => (string) env('DECLUTTER_SEED_ALLOW_LOCAL', '0'),
];

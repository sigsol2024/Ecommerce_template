<?php

/**
 * One-time VCP declutter catalogue import gates.
 * Used by Database\Seeders\DeclutterProductsSeeder.
 * Safe with `php artisan config:cache` (unlike raw env() outside config).
 */
return [
    // Axion-style: staff GET /admin* (and staff product pages) auto-import missing rows.
    // Set DECLUTTER_ADMIN_AUTO_IMPORT=0 to disable.
    'admin_auto_import' => (string) env('DECLUTTER_ADMIN_AUTO_IMPORT', '1'),

    // CLI seeder gates only (php artisan db:seed --class=...DeclutterProductsSeeder)
    'allow' => (string) env('DECLUTTER_SEED_ALLOW', '0'),
    'confirm_db' => (string) env('DECLUTTER_SEED_CONFIRM_DB', ''),
    'owner_email' => (string) env('DECLUTTER_SEED_OWNER_EMAIL', ''),
    'approver_email' => (string) env('DECLUTTER_SEED_APPROVER_EMAIL', ''),
    'allow_local' => (string) env('DECLUTTER_SEED_ALLOW_LOCAL', '0'),
];

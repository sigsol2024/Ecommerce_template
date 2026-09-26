<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyTables = [
            'wp_options',
            'wp_posts',
            'wp_postmeta',
            'wp_terms',
            'wp_termmeta',
            'wp_term_taxonomy',
            'wp_term_relationships',
            'wp_users',
            'wp_usermeta',
            'wp_comments',
            'wp_commentmeta',
        ];

        foreach ($legacyTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }

    public function down(): void
    {
        // Legacy WordPress tables are intentionally not recreated.
    }
};


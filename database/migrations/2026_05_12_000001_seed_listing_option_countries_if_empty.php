<?php

use Database\Seeders\ListingOptionCountriesSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listing_options') || ! Schema::hasTable('listing_option_categories')) {
            return;
        }

        (new ListingOptionCountriesSeeder)->run();
    }

    public function down(): void
    {
        // Intentionally no-op: country rows may be referenced by listings.
    }
};

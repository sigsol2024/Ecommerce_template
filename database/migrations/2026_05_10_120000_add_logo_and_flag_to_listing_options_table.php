<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listing_options')) {
            return;
        }

        Schema::table('listing_options', function (Blueprint $table) {
            if (! Schema::hasColumn('listing_options', 'logo_path')) {
                $table->string('logo_path', 512)->nullable()->after('value');
            }
            if (! Schema::hasColumn('listing_options', 'flag_emoji')) {
                $table->string('flag_emoji', 16)->nullable()->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('listing_options')) {
            return;
        }

        Schema::table('listing_options', function (Blueprint $table) {
            if (Schema::hasColumn('listing_options', 'flag_emoji')) {
                $table->dropColumn('flag_emoji');
            }
            if (Schema::hasColumn('listing_options', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        // Separate alters avoid flaky hasColumn checks inside a single Blueprint.
        if (! Schema::hasColumn('orders', 'inquiry_channel')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('inquiry_channel', 32)->nullable()->after('status');
            });
        }

        if (! Schema::hasColumn('orders', 'inquiry_preferred_date')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->date('inquiry_preferred_date')->nullable()->after('inquiry_channel');
            });
        }

        if (! Schema::hasColumn('orders', 'inquiry_preferred_slot')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('inquiry_preferred_slot', 32)->nullable()->after('inquiry_preferred_date');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        $cols = array_values(array_filter(
            ['inquiry_preferred_slot', 'inquiry_preferred_date', 'inquiry_channel'],
            fn (string $col) => Schema::hasColumn('orders', $col)
        ));

        if ($cols === []) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) use ($cols) {
            $table->dropColumn($cols);
        });
    }
};

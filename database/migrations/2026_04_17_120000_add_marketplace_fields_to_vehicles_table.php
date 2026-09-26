<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('condition', 20)->nullable()->after('body_type');
            $table->string('engine_size', 64)->nullable()->after('condition');
            $table->string('location', 255)->nullable()->after('engine_size');
            $table->json('features')->nullable()->after('location');

            $table->index('condition');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['condition']);
            $table->dropIndex(['location']);
            $table->dropColumn(['condition', 'engine_size', 'location', 'features']);
        });
    }
};

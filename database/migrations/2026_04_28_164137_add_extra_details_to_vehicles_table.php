<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedInteger('msrp')->nullable()->after('price');
            $table->unsignedSmallInteger('city_mpg')->nullable();
            $table->unsignedSmallInteger('hwy_mpg')->nullable();
            $table->string('engine_layout')->nullable();
            $table->string('top_track_speed')->nullable();
            $table->string('zero_to_sixty')->nullable();
            $table->string('number_of_gears')->nullable();
            $table->string('video_url', 2048)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'msrp',
                'city_mpg',
                'hwy_mpg',
                'engine_layout',
                'top_track_speed',
                'zero_to_sixty',
                'number_of_gears',
                'video_url'
            ]);
        });
    }
};

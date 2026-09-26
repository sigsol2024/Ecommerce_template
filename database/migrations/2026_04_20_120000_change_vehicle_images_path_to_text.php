<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `vehicle_images` MODIFY `path` TEXT NOT NULL');

            return;
        }

        Schema::table('vehicle_images', function (Blueprint $table) {
            $table->text('path')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `vehicle_images` MODIFY `path` VARCHAR(255) NOT NULL');

            return;
        }

        Schema::table('vehicle_images', function (Blueprint $table) {
            $table->string('path')->change();
        });
    }
};

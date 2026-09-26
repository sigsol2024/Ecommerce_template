<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Failed prior runs can leave a half-created table (errno 150 mid-ALTER).
        if (Schema::hasTable('vehicle_variants')) {
            Schema::drop('vehicle_variants');
        }

        Schema::create('vehicle_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 64)->nullable();
            // Indexes only — no InnoDB FK to listing_options. Shared MariaDB often returns
            // errno 150 for listing_options.id type/engine mismatches (same rationale as
            // 2026_05_02_120200 / product_category_listing_option_id).
            $table->unsignedBigInteger('size_listing_option_id')->nullable();
            $table->unsignedBigInteger('color_listing_option_id')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['vehicle_id', 'is_active']);
            $table->index('size_listing_option_id');
            $table->index('color_listing_option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_variants');
    }
};

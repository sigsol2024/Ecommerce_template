<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_option_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('listing_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('listing_option_categories')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('listing_options')->cascadeOnDelete();
            // 191: safe length for composite indexes with utf8mb4 on MySQL/MariaDB (avoids "Specified key was too long").
            $table->string('value', 191);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category_id', 'parent_id']);
            $table->index(['category_id', 'value']);
        });

        $now = now();
        $rows = [
            ['condition', 'Condition', 10],
            ['body_type', 'Body type', 20],
            ['make', 'Make', 30],
            ['model', 'Model', 40],
            ['transmission', 'Transmission', 50],
            ['fuel_type', 'Fuel type', 60],
            ['drive', 'Drive', 70],
            ['country', 'Country', 80],
        ];
        foreach ($rows as [$slug, $label, $order]) {
            DB::table('listing_option_categories')->insert([
                'slug' => $slug,
                'label' => $label,
                'sort_order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_options');
        Schema::dropIfExists('listing_option_categories');
    }
};

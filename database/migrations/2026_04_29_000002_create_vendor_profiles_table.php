<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name')->nullable();
            $table->string('public_email')->nullable();
            $table->string('public_phone', 64)->nullable();
            $table->string('public_address', 500)->nullable();
            $table->string('map_location', 500)->nullable();
            $table->boolean('show_on_listings')->default(true);
            $table->string('whatsapp', 64)->nullable();
            $table->string('website', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};

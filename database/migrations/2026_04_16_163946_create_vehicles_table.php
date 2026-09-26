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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('draft'); // draft|pending|approved|rejected

            $table->unsignedInteger('year')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();

            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('mileage')->nullable();

            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('drive')->nullable();
            $table->string('body_type')->nullable();

            $table->string('exterior_color')->nullable();
            $table->string('interior_color')->nullable();
            $table->string('vin')->nullable();

            $table->longText('description')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

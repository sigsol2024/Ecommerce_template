<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_sections')) {
            Schema::create('page_sections', function (Blueprint $table): void {
                $table->id();
                $table->string('page');
                $table->string('section_key');
                $table->string('content_type', 50)->default('text');
                $table->text('content')->nullable();
                $table->timestamps();
                $table->unique(['page', 'section_key']);
                $table->index('page');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table): void {
            if (! Schema::hasColumn('cms_pages', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('content_html');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_pages', function (Blueprint $table): void {
            if (Schema::hasColumn('cms_pages', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};


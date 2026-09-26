<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedInteger('finance_price')->nullable()->after('msrp');
            $table->decimal('finance_interest_rate', 5, 2)->nullable()->after('finance_price');
            $table->unsignedSmallInteger('finance_term_months')->nullable()->after('finance_interest_rate');
            $table->unsignedInteger('finance_down_payment')->nullable()->after('finance_term_months');

            $table->string('contact_phone', 64)->nullable()->after('location');
            $table->string('contact_address', 255)->nullable()->after('contact_phone');
            $table->string('contact_email')->nullable()->after('contact_address');
            $table->string('map_location', 255)->nullable()->after('contact_email');

            $table->longText('overview')->nullable()->after('description');
            $table->json('tech_specs')->nullable()->after('overview');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'finance_price',
                'finance_interest_rate',
                'finance_term_months',
                'finance_down_payment',
                'contact_phone',
                'contact_address',
                'contact_email',
                'map_location',
                'overview',
                'tech_specs',
            ]);
        });
    }
};


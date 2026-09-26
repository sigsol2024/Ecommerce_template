<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('country', 191)->nullable()->after('location');
            $table->text('street_address')->nullable()->after('country');
        });

        if (Schema::hasColumn('vehicles', 'contact_address') || Schema::hasColumn('vehicles', 'location')) {
            DB::table('vehicles')->orderBy('id')->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $street = trim((string) ($row->street_address ?? ''));
                    if ($street !== '') {
                        continue;
                    }
                    $ca = Schema::hasColumn('vehicles', 'contact_address') ? trim((string) ($row->contact_address ?? '')) : '';
                    $loc = Schema::hasColumn('vehicles', 'location') ? trim((string) ($row->location ?? '')) : '';
                    $merged = $ca !== '' ? $ca : ($loc !== '' ? $loc : '');
                    if ($merged !== '') {
                        DB::table('vehicles')->where('id', $row->id)->update(['street_address' => $merged]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['country', 'street_address']);
        });
    }
};

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
        if (Schema::hasTable('hotel_details') && !Schema::hasColumn('hotel_details', 'room_count')) {
            Schema::table('hotel_details', function (Blueprint $table) {
                $table->integer('room_count')->default(1)->after('check_out_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('hotel_details') && Schema::hasColumn('hotel_details', 'room_count')) {
            Schema::table('hotel_details', function (Blueprint $table) {
                $table->dropColumn('room_count');
            });
        }
    }
};

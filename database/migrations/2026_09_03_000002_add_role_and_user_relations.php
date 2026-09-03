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
        // 1. Add role column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
        });

        // 2. Add user relations to ticket_histories table
        Schema::table('ticket_histories', function (Blueprint $table) {
            $table->foreignId('booked_by_user_id')->nullable()->after('passenger_name')->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by_user_id')->nullable()->after('booked_by_user_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_histories', function (Blueprint $table) {
            $table->dropForeign(['booked_by_user_id']);
            $table->dropForeign(['paid_by_user_id']);
            $table->dropColumn(['booked_by_user_id', 'paid_by_user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

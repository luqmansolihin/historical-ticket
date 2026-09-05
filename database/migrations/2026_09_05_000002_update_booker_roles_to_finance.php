<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['booker', 'payer'])
            ->update(['role' => 'finance']);

        DB::table('ticket_status_logs')
            ->whereIn('user_role', ['booker', 'payer'])
            ->update(['user_role' => 'finance']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('role', 'finance')
            ->update(['role' => 'booker']);

        DB::table('ticket_status_logs')
            ->where('user_role', 'finance')
            ->update(['user_role' => 'booker']);
    }
};

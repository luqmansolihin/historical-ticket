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
            ->where('role', 'payer')
            ->update(['role' => 'booker']);

        DB::table('ticket_status_logs')
            ->where('user_role', 'payer')
            ->update(['user_role' => 'booker']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No automated revert necessary
    }
};

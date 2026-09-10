<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create header table: booking_histories
        Schema::create('booking_histories', function (Blueprint $table) {
            $table->id();
            $table->string('booking_type')->default('ticket')->index(); // 'ticket' or 'hotel'
            $table->string('booking_code')->nullable()->index(); // Ticket Code / Hotel Booking Code
            $table->string('invoice_code')->nullable()->unique();
            $table->date('booking_date')->index(); // Ticket Date / Hotel Booking Date
            $table->string('booked_by')->index();
            $table->foreignId('booked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('paid_by')->nullable()->index();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('Belum Bayar')->index(); // 'Belum Bayar', 'Lunas', 'Dibatalkan'
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        // 2. Create detail table for tickets: ticket_details
        Schema::create('ticket_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_history_id')->constrained('booking_histories')->cascadeOnDelete();
            $table->string('transport_type')->default('Pesawat')->index();
            $table->string('origin');
            $table->string('destination');
            $table->text('passenger_name');
            $table->timestamps();
        });

        // 3. Create detail table for hotels: hotel_details
        Schema::create('hotel_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_history_id')->constrained('booking_histories')->cascadeOnDelete();
            $table->string('hotel_name');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('room_count')->default(1);
            $table->text('guest_name');
            $table->timestamps();
        });

        // 4. Create status logs table: booking_status_logs
        Schema::create('booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_history_id')->constrained('booking_histories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name');
            $table->string('user_role')->nullable();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Data Migration from legacy ticket_histories & ticket_status_logs if they exist
        if (Schema::hasTable('ticket_histories')) {
            $oldTickets = DB::table('ticket_histories')->get();
            foreach ($oldTickets as $old) {
                $bookingHistoryId = DB::table('booking_histories')->insertGetId([
                    'booking_type' => 'ticket',
                    'booking_code' => $old->ticket_code ?? null,
                    'invoice_code' => $old->invoice_code ?? null,
                    'booking_date' => $old->ticket_date,
                    'booked_by' => $old->booked_by,
                    'booked_by_user_id' => $old->booked_by_user_id ?? null,
                    'paid_by' => $old->paid_by ?? null,
                    'paid_by_user_id' => $old->paid_by_user_id ?? null,
                    'payment_date' => $old->payment_date ?? null,
                    'amount' => $old->amount ?? 0,
                    'status' => $old->status ?? 'Belum Bayar',
                    'notes' => $old->notes ?? null,
                    'attachment_path' => $old->attachment_path ?? null,
                    'created_at' => $old->created_at ?? now(),
                    'updated_at' => $old->updated_at ?? now(),
                ]);

                DB::table('ticket_details')->insert([
                    'booking_history_id' => $bookingHistoryId,
                    'transport_type' => $old->transport_type ?? 'Pesawat',
                    'origin' => $old->origin ?? '-',
                    'destination' => $old->destination ?? '-',
                    'passenger_name' => $old->passenger_name ?? '-',
                    'created_at' => $old->created_at ?? now(),
                    'updated_at' => $old->updated_at ?? now(),
                ]);

                if (Schema::hasTable('ticket_status_logs')) {
                    $oldLogs = DB::table('ticket_status_logs')->where('ticket_history_id', $old->id)->get();
                    foreach ($oldLogs as $log) {
                        DB::table('booking_status_logs')->insert([
                            'booking_history_id' => $bookingHistoryId,
                            'user_id' => $log->user_id ?? null,
                            'user_name' => $log->user_name,
                            'user_role' => $log->user_role ?? null,
                            'from_status' => $log->from_status ?? null,
                            'to_status' => $log->to_status,
                            'notes' => $log->notes ?? null,
                            'created_at' => $log->created_at ?? now(),
                            'updated_at' => $log->updated_at ?? now(),
                        ]);
                    }
                }
            }

            Schema::dropIfExists('ticket_status_logs');
            Schema::dropIfExists('ticket_histories');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_status_logs');
        Schema::dropIfExists('hotel_details');
        Schema::dropIfExists('ticket_details');
        Schema::dropIfExists('booking_histories');
    }
};

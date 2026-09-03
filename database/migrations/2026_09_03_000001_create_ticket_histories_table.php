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
        Schema::create('ticket_histories', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->date('ticket_date');
            $table->string('origin');
            $table->string('destination');
            $table->string('transport_type');
            $table->string('passenger_name');
            $table->string('booked_by');
            $table->string('paid_by');
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('Lunas');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            // Indexes for fast searching & filtering
            $table->index('ticket_date');
            $table->index('transport_type');
            $table->index('booked_by');
            $table->index('paid_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_histories');
    }
};

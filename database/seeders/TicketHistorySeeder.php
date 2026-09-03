<?php

namespace Database\Seeders;

use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@ticket.com')->first();
        $bookerUser = User::where('email', 'booker@ticket.com')->first();
        $payerUser = User::where('email', 'payer@ticket.com')->first();
        $regularUser = User::where('email', 'user@ticket.com')->first();

        $tickets = [
            [
                'ticket_code' => 'GA-89102',
                'ticket_date' => '2026-08-15',
                'origin' => 'Jakarta (CGK)',
                'destination' => 'Surabaya (SUB)',
                'transport_type' => 'Pesawat',
                'passenger_name' => 'Luqman Solihin, Siti Nurhaliza',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-08-10',
                'amount' => 2900000.00,
                'status' => 'Lunas',
                'notes' => 'Penerbangan Garuda Indonesia GA-312 (2 Penumpang). Kunjungan Kerja Direksi ke Cabang Surabaya.',
            ],
            [
                'ticket_code' => 'KA-EX-4481',
                'ticket_date' => '2026-08-20',
                'origin' => 'Bandung (BD)',
                'destination' => 'Yogyakarta (YK)',
                'transport_type' => 'Kereta Api',
                'passenger_name' => 'Luqman Solihin, Ahmad Rifa\'i, Budi Santoso',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-08-18',
                'amount' => 1140000.00,
                'status' => 'Lunas',
                'notes' => 'Kereta Argo Wilis Executive Class (3 Penumpang). Perjalanan Dinas Tim IT Operations.',
            ],
            [
                'ticket_code' => 'QZ-39210',
                'ticket_date' => '2026-09-10',
                'origin' => 'Jakarta (CGK)',
                'destination' => 'Denpasar Bali (DPS)',
                'transport_type' => 'Pesawat',
                'passenger_name' => 'Luqman Solihin, Rina Wijaya',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => null,
                'amount' => 4200000.00,
                'status' => 'Belum Bayar',
                'notes' => 'AirAsia Flight QZ-392 (2 Penumpang). Menunggu persetujuan klaim anggaran Finance.',
            ],
            [
                'ticket_code' => 'TRV-DAYA-102',
                'ticket_date' => '2026-08-25',
                'origin' => 'Bandung (Dipatiukur)',
                'destination' => 'Jakarta (Fatmawati)',
                'transport_type' => 'Travel',
                'passenger_name' => 'Luqman Solihin',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-08-24',
                'amount' => 160000.00,
                'status' => 'Lunas',
                'notes' => 'DayTrans Executive Shuttle Seat 1A. Pembayaran perjalanan dinas atas nama Luqman Solihin.',
            ],
            [
                'ticket_code' => 'SJ-BUS-902',
                'ticket_date' => '2026-07-28',
                'origin' => 'Jakarta (Pulo Gebang)',
                'destination' => 'Semarang (Terboyo)',
                'transport_type' => 'Bus',
                'passenger_name' => 'Budi Santoso, Eko Prasetyo',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-07-25',
                'amount' => 440000.00,
                'status' => 'Lunas',
                'notes' => 'Bus Sinar Jaya Sleeper Suite Class (2 Penumpang).',
            ],
            [
                'ticket_code' => 'RNT-AVZ-003',
                'ticket_date' => '2026-08-01',
                'origin' => 'Yogyakarta (Adisutjipto)',
                'destination' => 'Magelang (Borobudur)',
                'transport_type' => 'Mobil / Rental',
                'passenger_name' => 'Luqman Solihin, Budi Santoso, Eko Prasetyo, Rina Wijaya',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-08-01',
                'amount' => 850000.00,
                'status' => 'Lunas',
                'notes' => 'Sewa Mobil Innova Reborn + Driver + BBM untuk Inspeksi Site Magelang (4 Penumpang).',
            ],
            [
                'ticket_code' => 'QG-6821',
                'ticket_date' => '2026-09-02',
                'origin' => 'Jakarta (HLP)',
                'destination' => 'Medan (KNO)',
                'transport_type' => 'Pesawat',
                'passenger_name' => 'Ahmad Rifa\'i',
                'booked_by' => 'Admin Manager',
                'booked_by_user_id' => $adminUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-09-01',
                'amount' => 1750000.00,
                'status' => 'Lunas',
                'notes' => 'Penerbangan Citilink QG-682 Kualanamu Medan.',
            ],
            [
                'ticket_code' => 'KPL-Dharma-08',
                'ticket_date' => '2026-06-12',
                'origin' => 'Surabaya (Tanjung Perak)',
                'destination' => 'Makassar (Soekarno-Hatta)',
                'transport_type' => 'Kapal Laut',
                'passenger_name' => 'Eko Prasetyo, Budi Santoso',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-06-10',
                'amount' => 1300000.00,
                'status' => 'Lunas',
                'notes' => 'KM Dharma Kencana VII Kelas VIP Room (2 Penumpang).',
            ],
            [
                'ticket_code' => 'JT-61209',
                'ticket_date' => '2026-05-04',
                'origin' => 'Surabaya (SUB)',
                'destination' => 'Balikpapan (BPN)',
                'transport_type' => 'Pesawat',
                'passenger_name' => 'Rina Wijaya',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'Rina Wijaya',
                'paid_by_user_id' => null,
                'payment_date' => '2026-05-01',
                'amount' => 1250000.00,
                'status' => 'Dibatalkan',
                'notes' => 'Lion Air JT-612 dibatalkan karena cuaca buruk dan di-refund 100%.',
            ]
        ];

        foreach ($tickets as $ticket) {
            TicketHistory::updateOrCreate(
                ['ticket_code' => $ticket['ticket_code']],
                $ticket
            );
        }
    }
}

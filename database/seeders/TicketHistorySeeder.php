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
                'passenger_name' => 'Luqman Solihin',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-08-10',
                'amount' => 1450000.00,
                'status' => 'Lunas',
                'notes' => 'Penerbangan Garuda Indonesia jam 08:00 WIB untuk Kunjungan Kerja Cabang Surabaya.',
            ],
            [
                'ticket_code' => 'KA-EX-4481',
                'ticket_date' => '2026-08-20',
                'origin' => 'Bandung (BD)',
                'destination' => 'Yogyakarta (YK)',
                'transport_type' => 'Kereta Api',
                'passenger_name' => 'Luqman Solihin',
                'booked_by' => 'Luqman Solihin',
                'booked_by_user_id' => $regularUser?->id,
                'paid_by' => 'Luqman Solihin',
                'paid_by_user_id' => $regularUser?->id,
                'payment_date' => '2026-08-18',
                'amount' => 380000.00,
                'status' => 'Reimburse',
                'notes' => 'Kereta Argo Wilis Executive Class. Nota klaim reimburse sudah diajukan ke HRD.',
            ],
            [
                'ticket_code' => 'SJ-BUS-902',
                'ticket_date' => '2026-07-28',
                'origin' => 'Jakarta (Pulo Gebang)',
                'destination' => 'Semarang (Terboyo)',
                'transport_type' => 'Bus',
                'passenger_name' => 'Budi Santoso',
                'booked_by' => 'Budi Santoso',
                'booked_by_user_id' => null,
                'paid_by' => 'Budi Santoso',
                'paid_by_user_id' => null,
                'payment_date' => '2026-07-25',
                'amount' => 220000.00,
                'status' => 'Lunas',
                'notes' => 'Bus Sinar Jaya Suite Class.',
            ],
            [
                'ticket_code' => 'TRV-DAYA-102',
                'ticket_date' => '2026-08-25',
                'origin' => 'Bandung (Dipatiukur)',
                'destination' => 'Jakarta (Fatmawati)',
                'transport_type' => 'Travel',
                'passenger_name' => 'Ahmad Rifa\'i',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-08-24',
                'amount' => 1350000.00,
                'status' => 'Lunas',
                'notes' => 'DayTrans Shuttle Executive seat 1A.',
            ],
            [
                'ticket_code' => 'QZ-39210',
                'ticket_date' => '2026-09-10',
                'origin' => 'Jakarta (CGK)',
                'destination' => 'Denpasar Bali (DPS)',
                'transport_type' => 'Pesawat',
                'passenger_name' => 'Luqman Solihin',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => null,
                'amount' => 2100000.00,
                'status' => 'Belum Bayar',
                'notes' => 'AirAsia Flight QZ-392. Invoice menunggu approval dari Finance Director.',
            ],
            [
                'ticket_code' => 'KPL-Dharma-08',
                'ticket_date' => '2026-06-12',
                'origin' => 'Surabaya (Tanjung Perak)',
                'destination' => 'Makassar (Soekarno-Hatta)',
                'transport_type' => 'Kapal Laut',
                'passenger_name' => 'Eko Prasetyo',
                'booked_by' => 'Eko Prasetyo',
                'booked_by_user_id' => null,
                'paid_by' => 'Eko Prasetyo',
                'paid_by_user_id' => null,
                'payment_date' => '2026-06-10',
                'amount' => 650000.00,
                'status' => 'Lunas',
                'notes' => 'KM Dharma Kencana VII - Kelas VIP.',
            ],
            [
                'ticket_code' => 'RNT-AVZ-003',
                'ticket_date' => '2026-08-01',
                'origin' => 'Yogyakarta (Adisutjipto)',
                'destination' => 'Magelang (Borobudur)',
                'transport_type' => 'Mobil / Rental',
                'passenger_name' => 'Tim Technical Operations (4 orang)',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $payerUser?->id,
                'payment_date' => '2026-08-01',
                'amount' => 750000.00,
                'status' => 'Lunas',
                'notes' => 'Sewa mobil Avanza + Driver + BBM untuk inspeksi perangkat.',
            ],
            [
                'ticket_code' => 'JT-61209',
                'ticket_date' => '2026-05-04',
                'origin' => 'Surabaya (SUB)',
                'destination' => 'Balikpapan (BPN)',
                'transport_type' => 'Pesawat',
                'passenger_name' => 'Rina Wijaya',
                'booked_by' => 'Rina Wijaya',
                'booked_by_user_id' => null,
                'paid_by' => 'Rina Wijaya',
                'paid_by_user_id' => null,
                'payment_date' => '2026-05-01',
                'amount' => 1250000.00,
                'status' => 'Dibatalkan',
                'notes' => 'Penerbangan Lion Air JT-612 dibatalkan karena cuaca buruk dan di-refund 100%.',
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

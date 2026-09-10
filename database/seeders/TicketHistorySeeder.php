<?php

namespace Database\Seeders;

use App\Models\BookingStatusLog;
use App\Models\TicketDetail;
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
        $financeUser = User::where('email', 'finance@ticket.com')->first() ?? $adminUser;
        $bookerUser = $financeUser;
        $payerUser = $financeUser;

        $tickets = [
            [
                'ticket_code' => 'GA-89102',
                'invoice_code' => 'INV-TCK-2026-001',
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
                'invoice_code' => 'INV-TCK-2026-002',
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
                'invoice_code' => 'INV-TCK-2026-003',
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
                'invoice_code' => 'INV-TCK-2026-004',
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
                'invoice_code' => 'INV-TCK-2026-005',
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
                'invoice_code' => 'INV-TCK-2026-006',
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
                'invoice_code' => 'INV-TCK-2026-007',
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
                'invoice_code' => 'INV-TCK-2026-008',
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
                'invoice_code' => 'INV-TCK-2026-009',
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

        foreach ($tickets as $tData) {
            $ticket = TicketHistory::updateOrCreate(
                ['invoice_code' => $tData['invoice_code']],
                [
                    'booking_type' => 'ticket',
                    'booking_code' => $tData['ticket_code'],
                    'invoice_code' => $tData['invoice_code'],
                    'booking_date' => $tData['ticket_date'],
                    'booked_by' => $tData['booked_by'],
                    'booked_by_user_id' => $tData['booked_by_user_id'],
                    'paid_by' => $tData['paid_by'],
                    'paid_by_user_id' => $tData['paid_by_user_id'],
                    'payment_date' => $tData['payment_date'],
                    'amount' => $tData['amount'],
                    'status' => $tData['status'],
                    'notes' => $tData['notes'],
                ]
            );

            TicketDetail::updateOrCreate(
                ['booking_history_id' => $ticket->id],
                [
                    'transport_type' => $tData['transport_type'],
                    'origin' => $tData['origin'],
                    'destination' => $tData['destination'],
                    'passenger_name' => $tData['passenger_name'],
                ]
            );

            if ($ticket->statusLogs()->count() === 0) {
                BookingStatusLog::create([
                    'booking_history_id' => $ticket->id,
                    'user_id' => $ticket->booked_by_user_id ?: $bookerUser?->id,
                    'user_name' => $ticket->booked_by,
                    'user_role' => 'finance',
                    'from_status' => null,
                    'to_status' => 'Belum Bayar',
                    'notes' => 'Tiket baru dibuat oleh Finance dengan status Belum Bayar.',
                ]);

                if ($ticket->status === 'Lunas') {
                    BookingStatusLog::create([
                        'booking_history_id' => $ticket->id,
                        'user_id' => $ticket->paid_by_user_id ?: $payerUser?->id,
                        'user_name' => $ticket->paid_by,
                        'user_role' => 'finance',
                        'from_status' => 'Belum Bayar',
                        'to_status' => 'Lunas',
                        'notes' => 'Pembayaran dikonfirmasi Lunas.',
                    ]);
                } elseif ($ticket->status === 'Dibatalkan') {
                    BookingStatusLog::create([
                        'booking_history_id' => $ticket->id,
                        'user_id' => $ticket->booked_by_user_id ?: $bookerUser?->id,
                        'user_name' => $ticket->booked_by,
                        'user_role' => 'finance',
                        'from_status' => 'Belum Bayar',
                        'to_status' => 'Dibatalkan',
                        'notes' => 'Tiket dibatalkan oleh Finance.',
                    ]);
                }
            }
        }
    }
}

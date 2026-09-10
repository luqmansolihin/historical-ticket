<?php

namespace Database\Seeders;

use App\Models\BookingStatusLog;
use App\Models\HotelDetail;
use App\Models\HotelHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class HotelHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $financeUser = User::where('email', 'finance@ticket.com')->first();
        $adminUser = User::where('email', 'admin@ticket.com')->first();
        $bookerUser = $financeUser ?? $adminUser;

        $hotels = [
            [
                'booking_code' => 'HTL-AST-881',
                'invoice_code' => 'INV-HTL-2026-001',
                'booking_date' => '2026-08-10',
                'hotel_name' => 'Aston Hotel & Convention Center Surabaya',
                'check_in_date' => '2026-08-15',
                'check_out_date' => '2026-08-18',
                'guest_name' => 'Luqman Solihin, Siti Nurhaliza',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $bookerUser?->id,
                'payment_date' => '2026-08-12',
                'amount' => 3850000.00,
                'status' => 'Lunas',
                'notes' => 'Reservasi 2 Deluxe Room (3 Malam) include breakfast untuk kunjungan kerja direksi.',
            ],
            [
                'booking_code' => 'HTL-HYT-102',
                'invoice_code' => 'INV-HTL-2026-002',
                'booking_date' => '2026-08-22',
                'hotel_name' => 'Grand Hyatt Bali Resort',
                'check_in_date' => '2026-09-10',
                'check_out_date' => '2026-09-14',
                'guest_name' => 'Luqman Solihin, Rina Wijaya, Budi Santoso',
                'booked_by' => 'Siti Nurhaliza (Sekretaris)',
                'booked_by_user_id' => $bookerUser?->id,
                'paid_by' => 'PT Corporate Finance',
                'paid_by_user_id' => $bookerUser?->id,
                'payment_date' => null,
                'amount' => 9200000.00,
                'status' => 'Belum Bayar',
                'notes' => 'Reservasi Ocean View Suite (4 Malam) untuk Tim R&D.',
            ],
            [
                'booking_code' => 'HTL-MRR-305',
                'invoice_code' => 'INV-HTL-2026-003',
                'booking_date' => '2026-07-15',
                'hotel_name' => 'JW Marriott Hotel Jakarta',
                'check_in_date' => '2026-07-20',
                'check_out_date' => '2026-07-22',
                'guest_name' => 'Ahmad Rifa\'i',
                'booked_by' => 'Admin Manager',
                'booked_by_user_id' => $adminUser?->id,
                'paid_by' => 'Ahmad Rifa\'i',
                'paid_by_user_id' => null,
                'payment_date' => '2026-07-18',
                'amount' => 2750000.00,
                'status' => 'Dibatalkan',
                'notes' => 'Dibatalkan karena perubahan jadwal rapat direksi.',
            ]
        ];

        foreach ($hotels as $hData) {
            $hotel = HotelHistory::updateOrCreate(
                ['invoice_code' => $hData['invoice_code']],
                [
                    'booking_type' => 'hotel',
                    'booking_code' => $hData['booking_code'],
                    'invoice_code' => $hData['invoice_code'],
                    'booking_date' => $hData['booking_date'],
                    'booked_by' => $hData['booked_by'],
                    'booked_by_user_id' => $hData['booked_by_user_id'],
                    'paid_by' => $hData['paid_by'],
                    'paid_by_user_id' => $hData['paid_by_user_id'],
                    'payment_date' => $hData['payment_date'],
                    'amount' => $hData['amount'],
                    'status' => $hData['status'],
                    'notes' => $hData['notes'],
                ]
            );

            HotelDetail::updateOrCreate(
                ['booking_history_id' => $hotel->id],
                [
                    'hotel_name' => $hData['hotel_name'],
                    'check_in_date' => $hData['check_in_date'],
                    'check_out_date' => $hData['check_out_date'],
                    'guest_name' => $hData['guest_name'],
                ]
            );

            if ($hotel->statusLogs()->count() === 0) {
                BookingStatusLog::create([
                    'booking_history_id' => $hotel->id,
                    'user_id' => $hotel->booked_by_user_id,
                    'user_name' => $hotel->booked_by,
                    'user_role' => 'finance',
                    'from_status' => null,
                    'to_status' => 'Belum Bayar',
                    'notes' => 'Histori hotel baru dibuat oleh Finance.',
                ]);

                if ($hotel->status === 'Lunas') {
                    BookingStatusLog::create([
                        'booking_history_id' => $hotel->id,
                        'user_id' => $hotel->paid_by_user_id,
                        'user_name' => $hotel->paid_by,
                        'user_role' => 'finance',
                        'from_status' => 'Belum Bayar',
                        'to_status' => 'Lunas',
                        'notes' => 'Pembayaran reservasi hotel dikonfirmasi Lunas.',
                    ]);
                } elseif ($hotel->status === 'Dibatalkan') {
                    BookingStatusLog::create([
                        'booking_history_id' => $hotel->id,
                        'user_id' => $hotel->booked_by_user_id,
                        'user_name' => $hotel->booked_by,
                        'user_role' => 'finance',
                        'from_status' => 'Belum Bayar',
                        'to_status' => 'Dibatalkan',
                        'notes' => 'Histori reservasi hotel dibatalkan.',
                    ]);
                }
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@ticket.com'],
            [
                'name' => 'Admin Manager',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Booker User (Sekretaris)
        $booker = User::firstOrCreate(
            ['email' => 'booker@ticket.com'],
            [
                'name' => 'Siti Nurhaliza (Sekretaris)',
                'password' => Hash::make('password'),
                'role' => 'booker',
            ]
        );

        // 3. Payer User (Finance)
        $payer = User::firstOrCreate(
            ['email' => 'payer@ticket.com'],
            [
                'name' => 'PT Corporate Finance',
                'password' => Hash::make('password'),
                'role' => 'payer',
            ]
        );

        // 4. Regular User (Passenger)
        $regularUser = User::firstOrCreate(
            ['email' => 'user@ticket.com'],
            [
                'name' => 'Luqman Solihin',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );

        $this->call([
            TicketHistorySeeder::class,
        ]);
    }
}

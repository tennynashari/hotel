<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Guest;

class GuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guests = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@email.com',
                'phone' => '081234567890',
                'id_card_type' => 'KTP',
                'id_card_number' => '3171234567890001',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'nationality' => 'Indonesia',
                'birth_date' => '1990-05-15',
                'is_repeat_guest' => true,
                'total_stays' => 3,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@email.com',
                'phone' => '081234567891',
                'id_card_type' => 'Passport',
                'id_card_number' => 'A12345678',
                'address' => '123 Main Street, New York',
                'nationality' => 'United States',
                'birth_date' => '1988-08-20',
                'is_repeat_guest' => false,
                'total_stays' => 1,
            ],
            [
                'name' => 'Ahmad Yani',
                'email' => 'ahmad.yani@email.com',
                'phone' => '081234567892',
                'id_card_type' => 'KTP',
                'id_card_number' => '3271234567890002',
                'address' => 'Jl. Asia Afrika No. 45, Bandung',
                'nationality' => 'Indonesia',
                'birth_date' => '1985-12-10',
                'is_repeat_guest' => true,
                'total_stays' => 5,
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@email.com',
                'phone' => '081234567893',
                'id_card_type' => 'Passport',
                'id_card_number' => 'B98765432',
                'address' => '456 Park Avenue, London',
                'nationality' => 'United Kingdom',
                'birth_date' => '1992-03-25',
                'is_repeat_guest' => false,
                'total_stays' => 0,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'phone' => '081234567894',
                'id_card_type' => 'KTP',
                'id_card_number' => '3571234567890003',
                'address' => 'Jl. Thamrin No. 78, Surabaya',
                'nationality' => 'Indonesia',
                'birth_date' => '1995-07-18',
                'is_repeat_guest' => false,
                'total_stays' => 1,
            ],
        ];

        foreach ($guests as $guest) {
            Guest::create($guest);
        }
    }
}

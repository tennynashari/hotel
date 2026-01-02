<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\Room;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Room Types
        $standard = RoomType::create([
            'name' => 'Standard',
            'description' => 'Standard room with basic amenities',
            'base_price' => 300000,
            'weekend_price' => 350000,
            'capacity' => 2,
            'facilities' => ['AC', 'TV', 'WiFi', 'Shower'],
            'is_active' => true,
        ]);

        $deluxe = RoomType::create([
            'name' => 'Deluxe',
            'description' => 'Deluxe room with premium amenities',
            'base_price' => 500000,
            'weekend_price' => 600000,
            'capacity' => 2,
            'facilities' => ['AC', 'TV', 'WiFi', 'Bathtub', 'Mini Bar', 'City View'],
            'is_active' => true,
        ]);

        $suite = RoomType::create([
            'name' => 'Suite',
            'description' => 'Spacious suite with living area',
            'base_price' => 800000,
            'weekend_price' => 950000,
            'capacity' => 4,
            'facilities' => ['AC', 'TV', 'WiFi', 'Bathtub', 'Mini Bar', 'City View', 'Living Room', 'Dining Area'],
            'is_active' => true,
        ]);

        // Create Sample Rooms
        // Standard Rooms (101-105)
        for ($i = 1; $i <= 5; $i++) {
            Room::create([
                'room_type_id' => $standard->id,
                'room_number' => '10' . $i,
                'floor' => '1',
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        // Deluxe Rooms (201-203)
        for ($i = 1; $i <= 3; $i++) {
            Room::create([
                'room_type_id' => $deluxe->id,
                'room_number' => '20' . $i,
                'floor' => '2',
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        // Suite Rooms (301-302)
        for ($i = 1; $i <= 2; $i++) {
            Room::create([
                'room_type_id' => $suite->id,
                'room_number' => '30' . $i,
                'floor' => '3',
                'status' => 'available',
                'is_active' => true,
            ]);
        }
    }
}

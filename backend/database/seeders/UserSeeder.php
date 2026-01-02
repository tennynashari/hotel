<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::where('name', 'owner')->first();
        $frontOfficeRole = Role::where('name', 'front_office')->first();
        $housekeepingRole = Role::where('name', 'housekeeping')->first();

        User::create([
            'role_id' => $ownerRole->id,
            'name' => 'Admin Owner',
            'email' => 'owner@hotel.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        User::create([
            'role_id' => $frontOfficeRole->id,
            'name' => 'Front Desk',
            'email' => 'frontdesk@hotel.com',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        User::create([
            'role_id' => $housekeepingRole->id,
            'name' => 'Housekeeping Staff',
            'email' => 'housekeeping@hotel.com',
            'phone' => '081234567892',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
    }
}

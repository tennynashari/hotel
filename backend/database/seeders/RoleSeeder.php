<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'owner',
                'display_name' => 'Owner',
                'description' => 'Full access to all features',
                'permissions' => ['all'],
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Manage operations and view reports',
                'permissions' => ['view_dashboard', 'view_reports', 'manage_bookings', 'manage_rooms', 'manage_staff'],
            ],
            [
                'name' => 'front_office',
                'display_name' => 'Front Office',
                'description' => 'Handle reservations, check-in, check-out',
                'permissions' => ['manage_bookings', 'manage_guests', 'view_rooms', 'process_payments'],
            ],
            [
                'name' => 'housekeeping',
                'display_name' => 'Housekeeping',
                'description' => 'Manage room cleaning and maintenance',
                'permissions' => ['view_tasks', 'update_room_status'],
            ],
            [
                'name' => 'accounting',
                'display_name' => 'Accounting',
                'description' => 'Handle payments and financial reports',
                'permissions' => ['process_payments', 'view_reports', 'manage_payments'],
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}

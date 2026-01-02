<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HousekeepingTask;
use App\Models\User;
use App\Models\Room;
use Carbon\Carbon;

class HousekeepingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $housekeeping = User::where('email', 'housekeeping@hotel.com')->first();

        // Task 1 - Room 102 needs cleaning (checked out)
        HousekeepingTask::create([
            'room_id' => 2, // Room 102
            'assigned_to' => $housekeeping->id,
            'task_type' => 'cleaning',
            'priority' => 'high',
            'status' => 'pending',
            'notes' => 'Guest checked out, full cleaning required',
        ]);

        // Update room status to dirty
        Room::find(2)->update(['status' => 'dirty']);

        // Task 2 - Room 201 maintenance
        HousekeepingTask::create([
            'room_id' => 6, // Room 201
            'assigned_to' => $housekeeping->id,
            'task_type' => 'maintenance',
            'priority' => 'normal',
            'status' => 'pending',
            'notes' => 'AC making unusual noise, needs inspection',
        ]);

        // Task 3 - Room 105 cleaning in progress
        HousekeepingTask::create([
            'room_id' => 5, // Room 105
            'assigned_to' => $housekeeping->id,
            'task_type' => 'cleaning',
            'priority' => 'normal',
            'status' => 'in_progress',
            'started_at' => Carbon::now()->subMinutes(30),
            'notes' => 'Daily cleaning',
        ]);

        // Update room status to cleaning
        Room::find(5)->update(['status' => 'cleaning']);

        // Task 4 - Room 202 completed
        HousekeepingTask::create([
            'room_id' => 7, // Room 202
            'assigned_to' => $housekeeping->id,
            'task_type' => 'cleaning',
            'priority' => 'normal',
            'status' => 'completed',
            'started_at' => Carbon::now()->subHours(2),
            'completed_at' => Carbon::now()->subHours(1),
            'notes' => 'Daily cleaning completed',
        ]);

        // Task 5 - Room 301 inspection
        HousekeepingTask::create([
            'room_id' => 9, // Room 301
            'assigned_to' => $housekeeping->id,
            'task_type' => 'inspection',
            'priority' => 'low',
            'status' => 'pending',
            'notes' => 'Routine inspection before guest arrival',
        ]);

        // Task 6 - Room 203 urgent cleaning
        HousekeepingTask::create([
            'room_id' => 8, // Room 203
            'assigned_to' => $housekeeping->id,
            'task_type' => 'cleaning',
            'priority' => 'urgent',
            'status' => 'pending',
            'notes' => 'Spill reported, needs immediate attention',
        ]);
    }
}

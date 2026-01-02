<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Payment;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $frontOffice = User::where('email', 'frontdesk@hotel.com')->first();
        
        // Booking 1 - Checked In
        $booking1 = Booking::create([
            'booking_number' => 'BK20260102001',
            'guest_id' => 1,
            'created_by' => $frontOffice->id,
            'source' => 'walk_in',
            'check_in_date' => Carbon::today(),
            'check_out_date' => Carbon::today()->addDays(2),
            'nights' => 2,
            'adults' => 2,
            'children' => 0,
            'status' => 'checked_in',
            'total_amount' => 600000,
            'deposit_amount' => 300000,
            'checked_in_at' => Carbon::now()->subHours(3),
        ]);

        BookingRoom::create([
            'booking_id' => $booking1->id,
            'room_id' => 1, // Room 101
            'room_rate' => 300000,
            'nights' => 2,
            'subtotal' => 600000,
        ]);

        // Update room status
        Room::find(1)->update(['status' => 'occupied']);

        Payment::create([
            'booking_id' => $booking1->id,
            'payment_number' => 'PAY20260102001',
            'payment_type' => 'deposit',
            'payment_method' => 'cash',
            'amount' => 300000,
            'processed_by' => $frontOffice->id,
        ]);

        // Booking 2 - Confirmed (upcoming)
        $booking2 = Booking::create([
            'booking_number' => 'BK20260102002',
            'guest_id' => 2,
            'created_by' => $frontOffice->id,
            'source' => 'phone',
            'check_in_date' => Carbon::today()->addDays(3),
            'check_out_date' => Carbon::today()->addDays(5),
            'nights' => 2,
            'adults' => 2,
            'children' => 1,
            'status' => 'confirmed',
            'total_amount' => 1000000,
            'deposit_amount' => 500000,
            'notes' => 'Late check-in expected',
        ]);

        BookingRoom::create([
            'booking_id' => $booking2->id,
            'room_id' => 6, // Room 201 (Deluxe)
            'room_rate' => 500000,
            'nights' => 2,
            'subtotal' => 1000000,
        ]);

        Payment::create([
            'booking_id' => $booking2->id,
            'payment_number' => 'PAY20260102002',
            'payment_type' => 'deposit',
            'payment_method' => 'transfer',
            'amount' => 500000,
            'reference_number' => 'TRF20260102001',
            'processed_by' => $frontOffice->id,
        ]);

        // Booking 3 - Checked Out (completed)
        $booking3 = Booking::create([
            'booking_number' => 'BK20260101001',
            'guest_id' => 3,
            'created_by' => $frontOffice->id,
            'source' => 'walk_in',
            'check_in_date' => Carbon::yesterday(),
            'check_out_date' => Carbon::today(),
            'nights' => 1,
            'adults' => 1,
            'children' => 0,
            'status' => 'checked_out',
            'total_amount' => 300000,
            'deposit_amount' => 0,
            'checked_in_at' => Carbon::yesterday()->setTime(14, 0),
            'checked_out_at' => Carbon::today()->setTime(11, 30),
        ]);

        BookingRoom::create([
            'booking_id' => $booking3->id,
            'room_id' => 2, // Room 102
            'room_rate' => 300000,
            'nights' => 1,
            'subtotal' => 300000,
        ]);

        Payment::create([
            'booking_id' => $booking3->id,
            'payment_number' => 'PAY20260101001',
            'payment_type' => 'full',
            'payment_method' => 'cash',
            'amount' => 300000,
            'processed_by' => $frontOffice->id,
        ]);

        // Booking 4 - Pending
        $booking4 = Booking::create([
            'booking_number' => 'BK20260102003',
            'guest_id' => 4,
            'created_by' => $frontOffice->id,
            'source' => 'website',
            'check_in_date' => Carbon::today()->addDays(7),
            'check_out_date' => Carbon::today()->addDays(10),
            'nights' => 3,
            'adults' => 2,
            'children' => 0,
            'status' => 'pending',
            'total_amount' => 2400000,
            'deposit_amount' => 0,
            'special_requests' => 'High floor, city view preferred',
        ]);

        BookingRoom::create([
            'booking_id' => $booking4->id,
            'room_id' => 9, // Room 301 (Suite)
            'room_rate' => 800000,
            'nights' => 3,
            'subtotal' => 2400000,
        ]);

        // Booking 5 - Checked In (multi-room)
        $booking5 = Booking::create([
            'booking_number' => 'BK20260102004',
            'guest_id' => 5,
            'created_by' => $frontOffice->id,
            'source' => 'phone',
            'check_in_date' => Carbon::today(),
            'check_out_date' => Carbon::today()->addDays(3),
            'nights' => 3,
            'adults' => 4,
            'children' => 2,
            'status' => 'checked_in',
            'total_amount' => 1800000,
            'deposit_amount' => 900000,
            'checked_in_at' => Carbon::now()->subHours(5),
            'notes' => 'Family vacation',
        ]);

        BookingRoom::create([
            'booking_id' => $booking5->id,
            'room_id' => 3, // Room 103
            'room_rate' => 300000,
            'nights' => 3,
            'subtotal' => 900000,
        ]);

        BookingRoom::create([
            'booking_id' => $booking5->id,
            'room_id' => 4, // Room 104
            'room_rate' => 300000,
            'nights' => 3,
            'subtotal' => 900000,
        ]);

        // Update room status
        Room::find(3)->update(['status' => 'occupied']);
        Room::find(4)->update(['status' => 'occupied']);

        Payment::create([
            'booking_id' => $booking5->id,
            'payment_number' => 'PAY20260102003',
            'payment_type' => 'deposit',
            'payment_method' => 'transfer',
            'amount' => 900000,
            'reference_number' => 'TRF20260102002',
            'processed_by' => $frontOffice->id,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['guest', 'rooms.roomType', 'payments', 'createdBy']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('check_in_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('check_out_date', '<=', $request->end_date);
        }

        // Search by guest name or booking number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'integer|min:0',
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'exists:rooms,id',
            'source' => 'nullable|string',
            'notes' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        // Calculate nights
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        // Check room availability
        foreach ($validated['room_ids'] as $roomId) {
            $isAvailable = $this->checkRoomAvailability(
                $roomId, 
                $validated['check_in_date'], 
                $validated['check_out_date']
            );
            
            if (!$isAvailable) {
                $room = Room::find($roomId);
                return response()->json([
                    'message' => "Room {$room->room_number} is not available for selected dates"
                ], 422);
            }
        }

        // Calculate total amount
        $totalAmount = 0;
        foreach ($validated['room_ids'] as $roomId) {
            $room = Room::with('roomType')->find($roomId);
            $totalAmount += $room->roomType->base_price * $nights;
        }

        // Create booking
        $booking = Booking::create([
            'booking_number' => $this->generateBookingNumber(),
            'guest_id' => $validated['guest_id'],
            'created_by' => auth()->id(),
            'source' => $validated['source'] ?? 'walk_in',
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'nights' => $nights,
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'deposit_amount' => $validated['deposit_amount'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        // Attach rooms
        foreach ($validated['room_ids'] as $roomId) {
            $room = Room::with('roomType')->find($roomId);
            $booking->rooms()->attach($roomId, [
                'room_rate' => $room->roomType->base_price,
                'nights' => $nights,
                'subtotal' => $room->roomType->base_price * $nights,
            ]);
        }

        return response()->json([
            'message' => 'Booking created successfully',
            'data' => $booking->load(['guest', 'rooms.roomType', 'payments'])
        ], 201);
    }

    public function show(Booking $booking)
    {
        return response()->json(
            $booking->load(['guest', 'rooms.roomType', 'payments', 'housekeepingTasks', 'createdBy'])
        );
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'check_in_date' => 'sometimes|date',
            'check_out_date' => 'sometimes|date|after:check_in_date',
            'adults' => 'sometimes|integer|min:1',
            'children' => 'integer|min:0',
            'notes' => 'nullable|string',
            'special_requests' => 'nullable|string',
        ]);

        // Recalculate nights if dates changed
        if (isset($validated['check_in_date']) || isset($validated['check_out_date'])) {
            $checkIn = Carbon::parse($validated['check_in_date'] ?? $booking->check_in_date);
            $checkOut = Carbon::parse($validated['check_out_date'] ?? $booking->check_out_date);
            $validated['nights'] = $checkIn->diffInDays($checkOut);
        }

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'data' => $booking->load(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function destroy(Booking $booking)
    {
        if (in_array($booking->status, ['checked_in', 'checked_out'])) {
            return response()->json([
                'message' => 'Cannot delete booking that has been checked in or out'
            ], 422);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully'
        ]);
    }

    public function confirm(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be confirmed'
            ], 422);
        }

        $booking->update(['status' => 'confirmed']);

        return response()->json([
            'message' => 'Booking confirmed successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function checkIn(Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed bookings can be checked in'
            ], 422);
        }

        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        // Update room status to occupied
        foreach ($booking->rooms as $room) {
            $room->update(['status' => 'occupied']);
        }

        return response()->json([
            'message' => 'Booking checked in successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function checkOut(Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return response()->json([
                'message' => 'Only checked-in bookings can be checked out'
            ], 422);
        }

        $booking->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        // Update room status to dirty
        foreach ($booking->rooms as $room) {
            $room->update(['status' => 'dirty']);
        }

        return response()->json([
            'message' => 'Booking checked out successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function cancel(Booking $booking)
    {
        if (in_array($booking->status, ['checked_in', 'checked_out', 'cancelled'])) {
            return response()->json([
                'message' => 'Cannot cancel this booking'
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_type_id' => 'nullable|exists:room_types,id',
        ]);

        $query = Room::with('roomType')
            ->where('is_active', true)
            ->where('status', '!=', 'out_of_order');

        if (isset($validated['room_type_id'])) {
            $query->where('room_type_id', $validated['room_type_id']);
        }

        $availableRooms = $query->get()->filter(function($room) use ($validated) {
            return $this->checkRoomAvailability(
                $room->id, 
                $validated['check_in_date'], 
                $validated['check_out_date']
            );
        });

        return response()->json($availableRooms->values());
    }

    private function checkRoomAvailability($roomId, $checkIn, $checkOut)
    {
        $hasConflict = Booking::whereHas('rooms', function($q) use ($roomId) {
            $q->where('room_id', $roomId);
        })
        ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
        ->where(function($q) use ($checkIn, $checkOut) {
            $q->whereBetween('check_in_date', [$checkIn, $checkOut])
              ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
              ->orWhere(function($q) use ($checkIn, $checkOut) {
                  $q->where('check_in_date', '<=', $checkIn)
                    ->where('check_out_date', '>=', $checkOut);
              });
        })
        ->exists();

        return !$hasConflict;
    }

    private function generateBookingNumber()
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "BK{$date}{$random}";
    }
}

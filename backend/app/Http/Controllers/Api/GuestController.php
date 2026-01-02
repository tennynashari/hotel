<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::withCount('bookings');

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id_card_number', 'like', "%{$search}%");
            });
        }

        // Filter by nationality
        if ($request->has('nationality')) {
            $query->where('nationality', $request->nationality);
        }

        $guests = $query->orderBy('created_at', 'desc')->get();

        return response()->json($guests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:guests,email',
            'phone' => 'required|string|max:20',
            'id_card_type' => 'nullable|in:ktp,passport,sim,other',
            'id_card_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
        ]);

        $guest = Guest::create($validated);

        return response()->json([
            'message' => 'Guest created successfully',
            'data' => $guest
        ], 201);
    }

    public function show(Guest $guest)
    {
        return response()->json(
            $guest->load(['bookings.rooms', 'bookings.payments'])
        );
    }

    public function update(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|unique:guests,email,' . $guest->id,
            'phone' => 'sometimes|string|max:20',
            'id_card_type' => 'nullable|in:ktp,passport,sim,other',
            'id_card_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
        ]);

        $guest->update($validated);

        return response()->json([
            'message' => 'Guest updated successfully',
            'data' => $guest
        ]);
    }

    public function destroy(Guest $guest)
    {
        // Check if guest has active bookings
        $hasActiveBookings = $guest->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->exists();

        if ($hasActiveBookings) {
            return response()->json([
                'message' => 'Cannot delete guest with active bookings'
            ], 422);
        }

        $guest->delete();

        return response()->json([
            'message' => 'Guest deleted successfully'
        ]);
    }

    public function search($query)
    {
        $guests = Guest::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        })
        ->limit(10)
        ->get();

        return response()->json($guests);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('roomType');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by room type
        if ($request->has('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        // Filter by floor
        if ($request->has('floor')) {
            $query->where('floor', $request->floor);
        }

        // Only active rooms by default
        if (!$request->has('include_inactive')) {
            $query->where('is_active', true);
        }

        $rooms = $query->orderBy('room_number')->get();

        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|unique:rooms,room_number',
            'floor' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Room created successfully',
            'data' => $room->load('roomType')
        ], 201);
    }

    public function show(Room $room)
    {
        return response()->json($room->load('roomType', 'housekeepingTasks'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_type_id' => 'sometimes|exists:room_types,id',
            'room_number' => 'sometimes|unique:rooms,room_number,' . $room->id,
            'floor' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $room->update($validated);

        return response()->json([
            'message' => 'Room updated successfully',
            'data' => $room->load('roomType')
        ]);
    }

    public function destroy(Room $room)
    {
        $room->update(['is_active' => false]);

        return response()->json([
            'message' => 'Room deactivated successfully'
        ]);
    }

    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,dirty,cleaning,out_of_order',
        ]);

        $room->update($validated);

        return response()->json([
            'message' => 'Room status updated successfully',
            'data' => $room
        ]);
    }

    public function roomTypes()
    {
        $roomTypes = RoomType::where('is_active', true)
            ->withCount('rooms')
            ->get();

        return response()->json($roomTypes);
    }

    public function statistics()
    {
        $stats = [
            'total_rooms' => Room::where('is_active', true)->count(),
            'available' => Room::where('status', 'available')->where('is_active', true)->count(),
            'occupied' => Room::where('status', 'occupied')->count(),
            'dirty' => Room::where('status', 'dirty')->count(),
            'cleaning' => Room::where('status', 'cleaning')->count(),
            'out_of_order' => Room::where('status', 'out_of_order')->count(),
            'occupancy_rate' => 0,
        ];

        if ($stats['total_rooms'] > 0) {
            $stats['occupancy_rate'] = round(($stats['occupied'] / $stats['total_rooms']) * 100, 2);
        }

        return response()->json($stats);
    }
}

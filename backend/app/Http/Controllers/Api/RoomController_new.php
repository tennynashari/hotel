<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('roomType')->where('is_active', true)->get();
        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|unique:rooms,room_number',
            'floor' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $room = Room::create($validated);
        return response()->json($room->load('roomType'), 201);
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
            'floor' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $room->update($validated);
        return response()->json($room->load('roomType'));
    }

    public function destroy(Room $room)
    {
        $room->update(['is_active' => false]);
        return response()->json(['message' => 'Room deactivated successfully']);
    }

    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,dirty,cleaning,out_of_order',
        ]);

        $room->update($validated);
        return response()->json($room);
    }

    public function roomTypes()
    {
        $roomTypes = RoomType::where('is_active', true)->get();
        return response()->json($roomTypes);
    }
}

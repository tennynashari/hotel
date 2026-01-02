<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $query = HousekeepingTask::with(['room.roomType', 'assignedUser']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by room
        if ($request->has('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by assigned user
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->orderBy('priority', 'asc')
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'task_type' => 'required|in:cleaning,maintenance,inspection,deep_clean',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $task = HousekeepingTask::create([
            'room_id' => $validated['room_id'],
            'task_type' => $validated['task_type'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'assigned_to' => $validated['assigned_to'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Housekeeping task created successfully',
            'data' => $task->load(['room.roomType', 'assignedUser'])
        ], 201);
    }

    public function show(HousekeepingTask $housekeeping)
    {
        return response()->json(
            $housekeeping->load(['room.roomType', 'assignedUser'])
        );
    }

    public function update(Request $request, HousekeepingTask $housekeeping)
    {
        $validated = $request->validate([
            'task_type' => 'sometimes|in:cleaning,maintenance,inspection,deep_clean',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $housekeeping->update($validated);

        return response()->json([
            'message' => 'Housekeeping task updated successfully',
            'data' => $housekeeping->load(['room.roomType', 'assignedUser'])
        ]);
    }

    public function destroy(HousekeepingTask $housekeeping)
    {
        if ($housekeeping->status === 'in_progress') {
            return response()->json([
                'message' => 'Cannot delete task that is in progress'
            ], 422);
        }

        $housekeeping->delete();

        return response()->json([
            'message' => 'Housekeeping task deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, HousekeepingTask $housekeeping)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'in_progress' && !$housekeeping->started_at) {
            $updates['started_at'] = now();
        }

        if ($validated['status'] === 'completed') {
            $updates['completed_at'] = now();
            
            // Update room status based on task type
            if ($housekeeping->task_type === 'cleaning') {
                $housekeeping->room->update(['status' => 'available']);
            }
        }

        $housekeeping->update($updates);

        return response()->json([
            'message' => 'Task status updated successfully',
            'data' => $housekeeping->fresh(['room.roomType', 'assignedUser'])
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total' => HousekeepingTask::count(),
            'pending' => HousekeepingTask::where('status', 'pending')->count(),
            'in_progress' => HousekeepingTask::where('status', 'in_progress')->count(),
            'completed_today' => HousekeepingTask::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'high_priority' => HousekeepingTask::where('priority', 'high')
                ->orWhere('priority', 'urgent')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];

        return response()->json($stats);
    }
}

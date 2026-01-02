<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\HousekeepingTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Room statistics
        $totalRooms = Room::count();
        $availableRooms = Room::where('status', 'available')->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();

        // Booking statistics
        $todayCheckIns = Booking::whereDate('check_in_date', $today)
            ->where('status', 'confirmed')
            ->count();
        
        $todayCheckOuts = Booking::whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
            ->count();

        $activeBookings = Booking::where('status', 'checked_in')->count();
        $pendingBookings = Booking::where('status', 'pending')->count();

        // Revenue statistics - only from FULL payments
        $todayFullPayments = Payment::whereDate('created_at', $today)
            ->where('payment_type', 'full')
            ->sum('amount');

        $monthFullPayments = Payment::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->where('payment_type', 'full')
            ->sum('amount');

        // All payment types statistics
        $todayAllPayments = Payment::whereDate('created_at', $today)
            ->sum('amount');

        $monthAllPayments = Payment::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->sum('amount');

        // Payment count by type today
        $todayPaymentCount = Payment::whereDate('created_at', $today)
            ->selectRaw('payment_type, count(*) as count, sum(amount) as total')
            ->groupBy('payment_type')
            ->get()
            ->keyBy('payment_type');

        // Housekeeping statistics
        $pendingTasks = HousekeepingTask::where('status', 'pending')->count();
        $inProgressTasks = HousekeepingTask::where('status', 'in_progress')->count();

        // Recent bookings
        $recentBookings = Booking::with(['guest', 'room.roomType'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'rooms' => [
                'total' => $totalRooms,
                'available' => $availableRooms,
                'occupied' => $occupiedRooms,
                'maintenance' => $maintenanceRooms,
            ],
            'bookings' => [
                'today_check_ins' => $todayCheckIns,
                'today_check_outs' => $todayCheckOuts,
                'active' => $activeBookings,
                'pending' => $pendingBookings,
            ],
            'revenue' => [
                'today_full_payments' => $todayFullPayments,
                'month_full_payments' => $monthFullPayments,
                'today_all_payments' => $todayAllPayments,
                'month_all_payments' => $monthAllPayments,
            ],
            'payments' => [
                'today_by_type' => $todayPaymentCount,
            ],
            'housekeeping' => [
                'pending' => $pendingTasks,
                'in_progress' => $inProgressTasks,
            ],
            'recent_bookings' => $recentBookings,
        ]);
    }
}

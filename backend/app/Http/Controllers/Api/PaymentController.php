<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['booking.guest', 'booking.room', 'processedBy']);

        // Filter by booking
        if ($request->has('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        // Filter by payment type
        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by payment number
        if ($request->has('search')) {
            $query->where('payment_number', 'like', '%' . $request->search . '%');
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_type' => 'required|in:deposit,partial,full,refund,extra_charge',
            'payment_method' => 'required|in:cash,transfer,qris,card,other',
            'amount' => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['processed_by'] = auth()->id();

        $payment = Payment::create($validated);
        $payment->load(['booking.guest', 'booking.room', 'processedBy']);

        return response()->json($payment, 201);
    }

    public function show(Payment $payment)
    {
        $payment->load(['booking.guest', 'booking.room.roomType', 'processedBy']);
        return response()->json($payment);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_type' => 'sometimes|required|in:deposit,partial,full,refund,extra_charge',
            'payment_method' => 'sometimes|required|in:cash,transfer,qris,card,other',
            'amount' => 'sometimes|required|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);
        $payment->load(['booking.guest', 'booking.room', 'processedBy']);

        return response()->json($payment);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully']);
    }

    public function bookingPayments($bookingId)
    {
        $booking = Booking::with(['guest', 'room.roomType', 'payments.processedBy'])
            ->findOrFail($bookingId);

        $totalPaid = $booking->payments->sum('amount');
        $totalAmount = $booking->subtotal;
        $balance = $totalAmount - $totalPaid;

        return response()->json([
            'booking' => $booking,
            'payments' => $booking->payments,
            'summary' => [
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'balance' => $balance,
            ],
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'guest_id',
        'created_by',
        'source',
        'check_in_date',
        'check_out_date',
        'nights',
        'adults',
        'children',
        'status',
        'total_amount',
        'deposit_amount',
        'notes',
        'special_requests',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'nights' => 'integer',
        'adults' => 'integer',
        'children' => 'integer',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    // Relationships
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'booking_rooms')
            ->withPivot('room_rate', 'nights', 'subtotal')
            ->withTimestamps();
    }

    // Helper to get primary room (first room)
    public function room()
    {
        return $this->belongsToMany(Room::class, 'booking_rooms')
            ->withPivot('room_rate', 'nights', 'subtotal')
            ->withTimestamps()
            ->limit(1);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Boot method to auto-generate booking number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BK' . date('Ymd') . strtoupper(Str::random(6));
            }
        });
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where('check_in_date', '>=', now()->toDateString());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'checked_in');
    }
}

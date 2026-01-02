<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'id_card_type',
        'id_card_number',
        'address',
        'nationality',
        'birth_date',
        'preferences',
        'is_repeat_guest',
        'total_stays',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'preferences' => 'array',
        'is_repeat_guest' => 'boolean',
        'total_stays' => 'integer',
    ];

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}

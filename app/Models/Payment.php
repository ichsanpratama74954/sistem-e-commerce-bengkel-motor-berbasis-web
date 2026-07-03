<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 
        'order_id', 
        'amount', 
        'payment_status',
        'payment_method',
    ];

    /**
     * Relasi balik ke tabel Bookings
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relasi balik ke tabel Orders
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
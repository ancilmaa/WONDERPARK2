<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'service', 'tier', 'price', 'visit_date', 'visit_time', 'payment_method',
        'customer_id', 'customer_name', 'customer_contact', 'pax', 'reservation_date', 'reservation_time',
        'package', 'notes', 'status', 'payment_method', 'receipt_path',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'reservation_date' => 'date',
    ];

    // Customer-facing booking flow (BookingController)
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'user_id');
    }

    // Admin reservations flow (ReservationController) — points to `users`,
    // NOT the empty `customers` table.
    public function customer()
    {
        return $this->belongsTo(\App\Models\User::class, 'customer_id', 'user_id');
    }

    /**
     * Unified accessors — a booking may come from either flow, so these
     * fall back to whichever set of columns actually has data. Use these
     * in views instead of reading the raw columns directly.
     */
    public function getDisplayCustomerAttribute()
    {
        return $this->customer ?? $this->user;
    }

    public function getDisplayDateAttribute()
    {
        return $this->reservation_date ?? $this->visit_date;
    }

    public function getDisplayTimeAttribute()
    {
        return $this->reservation_time ?? $this->visit_time;
    }

    public function getDisplayPaxAttribute()
    {
        return $this->pax ?? $this->tier;
    }
}
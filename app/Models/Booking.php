<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'service',
        'package',
        'tier',
        'price',
        'visit_date',
        'visit_time',
        'payment_method',
        'payment_proof_path',
        'payment_submitted_at',
        'payment_verified_at',
        'payment_rejection_reason',
        'status',
        'voucher_code',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'price' => 'decimal:2',
        'payment_submitted_at' => 'datetime',
        'payment_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->voucher_code)) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (self::where('voucher_code', $code)->exists());

                $booking->voucher_code = $code;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
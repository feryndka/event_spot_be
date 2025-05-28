<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\EventAttendee;
use App\Models\PaymentMethod;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attendee_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'payment_date',
        'midtrans_snap_token',
        'midtrans_order_id',
        'payment_details'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'payment_details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The attributes that should have default values.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending'
    ];

    /**
     * Get the attendee that owns the payment.
     */
    public function attendee()
    {
        return $this->belongsTo(EventAttendee::class, 'attendee_id');
    }

    /**
     * Get the event through the attendee.
     */
    public function event()
    {
        return $this->hasOneThrough(Event::class, EventAttendee::class, 'id', 'id', 'attendee_id', 'event_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }
}

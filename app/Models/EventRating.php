<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'rating',
        'review',
        'is_approved',
        'reviewed_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'reviewed_at' => 'datetime'
    ];

    /**
     * Get the event that owns the rating.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that made the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include approved ratings.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include ratings within a specific range.
     */
    public function scopeRatingRange($query, $min, $max)
    {
        return $query->whereBetween('rating', [$min, $max]);
    }

    /**
     * Scope a query to only include ratings with reviews.
     */
    public function scopeWithReviews($query)
    {
        return $query->whereNotNull('review');
    }
}

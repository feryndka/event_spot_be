<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'event_id',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime'
  ];

  /**
   * Get the user that owns the bookmark.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the event that is bookmarked.
   */
  public function event(): BelongsTo
  {
    return $this->belongsTo(Event::class);
  }
}

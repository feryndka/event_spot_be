<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'event_id',
  ];

  /**
   * Get the user that owns the bookmark.
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the event that is bookmarked.
   */
  public function event()
  {
    return $this->belongsTo(Event::class);
  }
}

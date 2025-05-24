<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
  use HasFactory;

  protected $fillable = [
    'event_id',
    'image_path',
    'image_type',
    'is_primary',
    'order',
  ];

  protected $casts = [
    'is_primary' => 'boolean',
    'order' => 'integer',
  ];

  /**
   * Get the event that owns the image.
   */
  public function event()
  {
    return $this->belongsTo(Event::class);
  }
}

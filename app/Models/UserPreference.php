<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'user_id',
    'email_notifications',
    'push_notifications',
    'event_reminders',
    'preferred_categories',
    'preferred_locations'
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'email_notifications' => 'boolean',
    'push_notifications' => 'boolean',
    'event_reminders' => 'boolean',
    'preferred_categories' => 'array',
    'preferred_locations' => 'array',
    'created_at' => 'datetime',
    'updated_at' => 'datetime'
  ];

  /**
   * The attributes that should have default values.
   *
   * @var array
   */
  protected $attributes = [
    'email_notifications' => true,
    'push_notifications' => true,
    'event_reminders' => true
  ];

  /**
   * Get the user that owns the preferences.
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }
}

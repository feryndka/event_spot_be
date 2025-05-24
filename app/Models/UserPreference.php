<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'notification_preferences',
    'privacy_settings',
    'theme_preference',
    'language_preference',
  ];

  protected $casts = [
    'notification_preferences' => 'array',
    'privacy_settings' => 'array',
  ];

  /**
   * Get the user that owns the preferences.
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }
}

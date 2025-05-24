<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
  use HasFactory;

  protected $fillable = [
    'follower_id',
    'following_id',
  ];

  /**
   * Get the user that is following.
   */
  public function follower()
  {
    return $this->belongsTo(User::class, 'follower_id');
  }

  /**
   * Get the user that is being followed.
   */
  public function following()
  {
    return $this->belongsTo(User::class, 'following_id');
  }
}

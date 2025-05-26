<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'promotor_id',
  ];

  /**
   * Get the user that is following.
   */
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Get the promotor that is being followed.
   */
  public function promotor()
  {
    return $this->belongsTo(User::class, 'promotor_id');
  }
}

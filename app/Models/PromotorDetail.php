<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotorDetail extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id',
    'company_name',
    'company_logo',
    'description',
    'website',
    'social_media',
    'verification_status',
    'verification_document'
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'social_media' => 'array',
    'verification_status' => 'string'
  ];

  /**
   * Get the user that owns the promotor detail.
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the events for the promotor.
   */
  public function events()
  {
    return $this->hasMany(Event::class, 'promotor_id', 'user_id');
  }

  /**
   * Get the followers for the promotor.
   */
  public function followers()
  {
    return $this->hasMany(Follower::class, 'promotor_id', 'user_id');
  }
}

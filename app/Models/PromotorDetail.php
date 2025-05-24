<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotorDetail extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'company_name',
    'company_address',
    'phone_number',
    'website',
    'description',
    'verification_status',
    'verification_document',
    'verification_notes',
  ];

  protected $casts = [
    'verification_status' => 'string',
  ];

  /**
   * Get the user that owns the promotor details.
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
}

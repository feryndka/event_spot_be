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
    'company_logo',
    'description',
    'website',
    'social_media',
    'verification_status',
    'verification_document',
  ];

  protected $casts = [
    'social_media' => 'array',
    'verification_status' => 'string',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function events()
  {
    return $this->hasManyThrough(Event::class, User::class, 'id', 'promotor_id');
  }
}

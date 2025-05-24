<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorySubscription extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'category_id',
  ];

  /**
   * Get the user that owns the subscription.
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the category that is subscribed to.
   */
  public function category()
  {
    return $this->belongsTo(Category::class);
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Statistic extends Model
{
  use HasFactory;

  protected $fillable = [
    'event_id',
    'page_views',
    'unique_visitors',
    'engagement_rate',
    'click_through_rate',
    'data_date',
  ];

  protected $casts = [
    'page_views' => 'integer',
    'unique_visitors' => 'integer',
    'engagement_rate' => 'decimal:2',
    'click_through_rate' => 'decimal:2',
    'data_date' => 'date',
  ];

  public function event(): BelongsTo
  {
    return $this->belongsTo(Event::class);
  }
}

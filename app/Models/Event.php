<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'slug',
    'description',
    'is_ai_generated',
    'poster_image',
    'promotor_id',
    'category_id',
    'location_name',
    'address',
    'latitude',
    'longitude',
    'start_date',
    'end_date',
    'registration_start',
    'registration_end',
    'is_free',
    'price',
    'max_attendees',
    'is_published',
    'is_featured',
    'is_approved',
    'views_count',
  ];

  protected $casts = [
    'is_ai_generated' => 'boolean',
    'is_free' => 'boolean',
    'is_published' => 'boolean',
    'is_featured' => 'boolean',
    'is_approved' => 'boolean',
    'start_date' => 'datetime',
    'end_date' => 'datetime',
    'registration_start' => 'datetime',
    'registration_end' => 'datetime',
    'price' => 'decimal:2',
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8',
  ];

  public function promotor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'promotor_id');
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  public function attendees(): HasMany
  {
    return $this->hasMany(EventAttendee::class);
  }

  public function comments(): HasMany
  {
    return $this->hasMany(Comment::class);
  }

  public function bookmarks(): HasMany
  {
    return $this->hasMany(Bookmark::class);
  }

  public function statistics(): HasMany
  {
    return $this->hasMany(Statistic::class);
  }

  public function tags(): BelongsToMany
  {
    return $this->belongsToMany(EventTag::class, 'event_tag_relations', 'event_id', 'tag_id');
  }

  public function images(): HasMany
  {
    return $this->hasMany(EventImage::class);
  }

  // Scopes
  public function scopePublished($query)
  {
    return $query->where('is_published', true);
  }

  public function scopeFeatured($query)
  {
    return $query->where('is_featured', true);
  }

  public function scopeApproved($query)
  {
    return $query->where('is_approved', true);
  }

  public function scopeFree($query)
  {
    return $query->where('is_free', true);
  }

  public function scopePaid($query)
  {
    return $query->where('is_free', false);
  }

  public function scopeUpcoming($query)
  {
    return $query->where('start_date', '>', now());
  }

  public function scopeOngoing($query)
  {
    return $query->where('start_date', '<=', now())
      ->where('end_date', '>=', now());
  }

  public function scopePast($query)
  {
    return $query->where('end_date', '<', now());
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'profile_picture',
        'bio',
        'user_type',
        'is_verified',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function eventAttendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'bookmarks')
            ->withTimestamps();
    }

    public function favoriteEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_favorites')
            ->withTimestamps();
    }

    public function followingPromotors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'promotor_follows', 'follower_id', 'promotor_id')
            ->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'promotor_follows', 'promotor_id', 'follower_id')
            ->withTimestamps();
    }

    public function subscribedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_subscriptions')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isPromotor(): bool
    {
        return $this->is_promotor;
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function isSuspended(): bool
    {
        return $this->is_suspended;
    }

    public function promotorDetail()
    {
        return $this->hasOne(PromotorDetail::class);
    }

    // Scopes
    public function scopePromotors($query)
    {
        return $query->where('user_type', 'promotor');
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

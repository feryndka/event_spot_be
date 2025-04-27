<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
    public function promotorDetails()
    {
        return $this->hasOne(PromotorDetail::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'promotor_id');
    }

    public function eventAttendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function followers()
    {
        return $this->hasMany(Follower::class, 'promotor_id');
    }

    public function following()
    {
        return $this->hasMany(Follower::class, 'user_id');
    }

    public function categorySubscriptions()
    {
        return $this->hasMany(CategorySubscription::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function userPreferences()
    {
        return $this->hasOne(UserPreference::class);
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

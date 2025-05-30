<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Check if token is expired
     */
    public function isExpired($minutes = 60)
    {
        return now()->diffInMinutes($this->created_at) > $minutes;
    }

    /**
     * Verify token
     */
    public function verifyToken($token)
    {
        return \Hash::check($token, $this->token);
    }
}
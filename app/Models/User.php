<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'udomain',
        'password',
        'face_image_path',
    ];

    protected $hidden = [
        'password',
    ];

     protected static function booted()
    {
        static::created(function ($user) {
            Taker::create([
                'user_id' => $user->id,
            ]);
        });
    }

    // User booking locker
    public function lockerSessions()
    {
        return $this->hasMany(LockerSession::class);
    }

        // Notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}

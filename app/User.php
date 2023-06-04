<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function getKids() {
        return $this->hasMany(Kid::class);
    }
    public function getEvents() {
        return $this->hasMany(Event::class);
    }
    public function getImages() {
        return $this->hasMany(Image::class);
    }
    public function getAttendees() {
        return $this->hasMany(Attendee::class);
    }
    public function getBookings() {
        return $this->hasMany(Booking::class);
    }
    public function getMessages() {
        return $this->hasMany(Message::class);
    }
    public function getNotifications() {
        return $this->hasMany(Notification::class);
    }
}

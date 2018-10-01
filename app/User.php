<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Hamedmehryar\SessionTracker\Traits\SessionTrackerUserTrait;
use Laravel\Passport\HasApiTokens;
use Storage;

class User extends Authenticatable
{
    // use Notifiable, SessionTrackerUserTrait, HasApiTokens;
    use Notifiable , HasApiTokens;
    protected $table = 'cms_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'photo', 'phone_number', 'gender', 'specialization', 'educational_level', 'address', 'id_cms_privileges', 'created_by',
    ];

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute()
    {
        return Storage::url('avatars/'.$this->id.'/'.$this->avatar);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}

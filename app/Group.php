<?php

namespace App;

use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Hamedmehryar\SessionTracker\Traits\SessionTrackerUserTrait;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Storage;

class Group extends Model
{
    // use Notifiable, SessionTrackerUserTrait, HasApiTokens;
    use Notifiable , HasApiTokens;
    protected $table = 'groups';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password', 'photo', 'phone_number', 'gender', 'specialization', 'educational_level', 'address', 'id_cms_privileges',
    // ];

    // protected $appends = ['avatar_url'];
    //
    // public function getAvatarUrlAttribute()
    // {
    //     return Storage::url('avatars/'.$this->id.'/'.$this->avatar);
    // }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];
}

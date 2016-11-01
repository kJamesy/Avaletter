<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'username', 'email', 'password', 'active', 'meta'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * One to Many User to Email relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emails()
    {
        return $this->hasMany('App\Email');
    }

    /**
     * Cache the supplied settings for the supplied user; forever
     * @param $userId
     * @param array $cacheKeys
     * @param array $cacheValues
     */
    public static function cacheSettings($userId, $cacheKeys = [], $cacheValues = [])
    {
        foreach( $cacheKeys as $key => $cacheKey ) {
            cache()->forget('user_' . $userId . '_' . $cacheKey);
            cache()->forever('user_' . $userId . '_' . $cacheKey, $cacheValues[$key]);
        }
    }
}

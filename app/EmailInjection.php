<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailInjection extends Model
{
    protected $table = 'email_injections';

    /**
     * Inverse of One to Many Subscriber relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriber()
    {
        return $this->belongsTo('App\Subscriber');
    }

    /**
     * Inverse of One to Many Email relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function email()
    {
        return $this->belongsTo('App\Email');
    }

    /**
     * One to One EmailDelivery relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function email_delivery()
    {
        return $this->hasOne('App\EmailDelivery');
    }

    /**
     * One to One EmailOpen relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function email_open()
    {
        return $this->hasOne('App\EmailOpen');
    }

    /**
     * One to Many EmailClick relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function email_clicks()
    {
        return $this->hasMany('App\EmailClick');
    }

}

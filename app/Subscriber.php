<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscribers';

    /**
     * Define Many to Many MailingList relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mailing_lists()
    {
        return $this->belongsToMany('App\MailingList', 'mailing_list_subscriber', 'subscriber_id', 'mailing_list_id');
    }
}

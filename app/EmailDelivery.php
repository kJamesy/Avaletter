<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailDelivery extends Model
{
    protected $table = 'email_deliveries';

    /**
     * Inverse of One to One EmailInjection relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function email_injection()
    {
        return $this->belongsTo('App\EmailInjection');
    }

}

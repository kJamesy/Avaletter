<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailClick extends Model
{
    protected $table = 'email_clicks';

    /**
     * Inverse of One to Many EmailInjection relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function email_injection()
    {
        return $this->belongsTo('App\EmailInjection');
    }
}

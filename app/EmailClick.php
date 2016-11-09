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

    /**
     * Find an email click record by injection id and target_link
     * @param $injectionId
     * @param $target_link
     * @return mixed
     */
    public static function findClick($injectionId, $target_link)
    {
        return static::where('email_injection_id', $injectionId)->where('target_link', $target_link)->first();
    }

}

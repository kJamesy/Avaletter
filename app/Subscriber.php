<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    /**
     * Database table
     * @var string
     */
    protected $table = 'subscribers';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'meta'];

    /**
     * Validation rules
     * @var array
     */
    public static $rules = ['first_name' => 'required|max:255','last_name' => 'required|max:255', 'email' => 'required|email|max:255|unique:subscribers'];

    /**
     * Define Many to Many MailingList relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mailing_lists()
    {
        return $this->belongsToMany('App\MailingList', 'mailing_list_subscriber', 'subscriber_id', 'mailing_list_id');
    }

    /**
     * @param string $orderBy
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public static function getSubscribers($orderBy = 'created_at', $order = 'desc', $paginate = 1000)
    {
        return static::with('mailing_lists')->orderBy($orderBy, $order)->paginate($paginate);
    }
}

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
     * Cast the active column value to a boolean
     * @var array
     */
    protected $casts = ['active' => 'boolean'];

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
     * Find specified active subscriber
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getActiveSubscriber($id)
    {
        return static::with('mailing_lists')->where('is_deleted', 0)->find($id);
    }

    /**
     * Find specified subscriber
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getSubscriber($id)
    {
        return static::with('mailing_lists')->find($id);
    }

    /**
     * Get subscribers
     * @param string $orderBy
     * @param string $order
     * @param int $paginate
     * @param int $mailingList
     * @param int $trash
     * @return mixed
     */
    public static function getSubscribers($orderBy = 'created_at', $order = 'desc', $paginate = 1000, $mailingList = 0, $trash = 0)
    {
        if ( $mailingList )
            return static::with('mailing_lists')->whereHas('mailing_lists', function($query) use($mailingList) {
                $query->where('mailing_lists.id', $mailingList);
            })->where('is_deleted', $trash)->orderBy($orderBy, $order)->paginate($paginate);
        else
            return static::with('mailing_lists')->where('is_deleted', $trash)->orderBy($orderBy, $order)->paginate($paginate);
    }


    /**
     * Get all subscribers
     * @param string $orderBy
     * @param string $order
     * @param int $mailingList
     * @param int $trash
     * @return mixed
     */
    public static function getAllSubscribers($orderBy = 'created_at', $order = 'asc', $mailingList = 0, $trash = 0)
    {
        if ( $mailingList )
            return static::with('mailing_lists')->whereHas('mailing_lists', function($query) use($mailingList) {
                $query->where('mailing_lists.id', $mailingList);
            })->where('is_deleted', $trash)->orderBy($orderBy, $order)->get();
        else
            return static::with('mailing_lists')->where('is_deleted', $trash)->orderBy($orderBy, $order)->get();
    }
}

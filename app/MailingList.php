<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailingList extends Model
{

    /**
     * Database table
     * @var string
     */
    protected $table = 'mailing_lists';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['name', 'meta'];

    /**
     * Validation rules
     * @var array
     */
    public static $rules = ['name' => 'required|unique:mailing_lists|max:255'];

    /**
     * Define Many to Many subscriber relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscribers()
    {
        return $this->belongsToMany('App\Subscriber', 'mailing_list_subscriber', 'mailing_list_id', 'subscriber_id');
    }

    /**
     *
     * @param $id
     * @return mixed
     */
    public static function getMailingList($id)
    {
        return static::find($id);
    }

    /**
     * Get mailing list resource
     * @param string $orderBy
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public static function getMailingLists($orderBy = 'created_at', $order = 'desc', $paginate = 1000)
    {
        return static::withCount(['subscribers' => function($query) {
            $query->where('subscribers.is_deleted', 0);
        }])->orderBy($orderBy, $order)->paginate($paginate);
    }

    /**
     * Get a list of mailing lists
     * @return mixed
     */
    public static function getMailingListsList()
    {
        return static::orderBy('name')->get(['id', 'name']);
    }

    /**
     * Get a list of mailing lists that have at least one subscriber attached
     * @return mixed
     */
    public static function getAttachedMailingListsList($includeDeleted = false)
    {
        if ( $includeDeleted )
            return static::has('subscribers', '>=', 1)->orderBy('name')->get(['id', 'name']);
        else
            return static::whereHas('subscribers', function($query) {
                $query->where('subscribers.is_deleted', 0);
            })->orderBy('name')->get(['id', 'name']);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Subscriber extends Model
{
    use Searchable;

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
        return $this->belongsToMany(MailingList::class, 'mailing_list_subscriber', 'subscriber_id', 'mailing_list_id');
    }

    /**
     * One to Many EmailInjection relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function email_injections()
    {
        return $this->hasMany(EmailInjection::class);
    }

    /**
     * A subscriber has many deliveries through email injections
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function email_deliveries()
    {
        return $this->hasManyThrough(EmailDelivery::class, EmailInjection::class);
    }

    /**
     * A subscriber has many email opens through email injections
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function email_opens()
    {
        return $this->hasManyThrough(EmailOpen::class, EmailInjection::class);
    }

    /**
     * A subscriber has many email clicks through email injections
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function email_clicks()
    {
        return $this->hasManyThrough(EmailClick::class, EmailInjection::class);
    }

    /**
     * Find specified active subscriber
     * @param $id
     * @return mixed
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

    /**
     * Get subscribers that can be emailed
     * @return mixed
     */
    public static function getEmailableSubscribersList()
    {
        return static::where('is_deleted', 0)->where('active', 1)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
    }

    /**
     * Get specified subscribers
     * @param $ids
     * @param string $orderBy
     * @param string $order
     * @return mixed
     */
    public static function getSpecifiedSubscribers($ids, $orderBy = 'created_at', $order = 'asc')
    {
        return static::with('mailing_lists')->whereIn('id', $ids)->orderBy($orderBy, $order)->get();
    }

    /**
     * Get search results
     * @param $search
     * @param int $paginate
     * @param int $trash
     * @return mixed
     */
    public static function getSearchResults($search, $paginate = 1000, $trash = 0)
    {
        return static::search($search)->where('is_deleted', $trash)->paginate($paginate);
    }

    /**
     * Fetch subscribers by ids who can be emailed
     * @param $ids
     * @param $returnIds
     * @return mixed
     */
    public static function getEmailableSubscibersByIds($ids, $returnIds = true)
    {
        return $returnIds
            ? static::where('is_deleted', 0)->where('active', 1)->whereIn('id', (array) $ids)->pluck('id')
            : static::where('is_deleted', 0)->where('active', 1)->whereIn('id', (array) $ids)->get(['id', 'first_name', 'last_name', 'email']);
    }

    /**
     * Fetch subscribers by mailing list ids except given subscribers ids who can be emailed
     * @param $mListIds
     * @param $except
     * @return mixed
     */
    public static function getEmailableSubscribersByMLists($mListIds, $except)
    {
        return static::whereHas('mailing_lists', function($query) use($mListIds) {
            $query->whereIn('mailing_lists.id', (array) $mListIds);
        })->where('subscribers.is_deleted', 0)->where('subscribers.active', 1)->whereNotIn('subscribers.id', (array) $except)->get(['id', 'first_name', 'last_name', 'email']);
    }

    /**
     * Get Subscriber by email
     * @param $email
     * @return mixed
     */
    public static function getSubscriberByEmail($email)
    {
        return static::where('email', $email)->first();
    }

    /**
     * Get injected subscribers for specified email id
     * @param $emailId
     * @return mixed
     */
    public static function getInjectedSubscribers($emailId)
    {
        return static::with(['email_deliveries' => function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            })->select('email_deliveries.delivered_at');
        }])->with(['email_opens' => function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            })->select('email_opens.opened_at');
        }])->with(['email_clicks' => function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            })->select('email_clicks.id');
        }])->whereHas('email_injections', function($q) use ( $emailId ) {
            $q->where('email_id', $emailId);
        })->orderBy('subscribers.first_name', 'asc')->paginate(100);
    }

    /**
     * Get delivered subscribers for specified email id
     * @param $emailId
     * @return mixed
     */
    public static function getDeliveredSubscribers($emailId)
    {
        return static::with(['email_deliveries' => function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            })->select('email_deliveries.delivered_at');
        }])->whereHas('email_injections', function($q) use ( $emailId ) {
            $q->where('email_id', $emailId);
        })->whereHas('email_deliveries', function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            });
        })->orderBy('subscribers.first_name', 'asc')->paginate(100);
    }

    /**
     * Get opened subscribers for specified email id
     * @param $emailId
     * @return mixed
     */
    public static function getOpenedSubscribers($emailId)
    {
        return static::with(['email_opens' => function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            })->select('email_opens.opened_at');
        }])->whereHas('email_injections', function($q) use ( $emailId ) {
            $q->where('email_id', $emailId);
        })->whereHas('email_opens', function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            });
        })->orderBy('subscribers.first_name', 'asc')->paginate(100);
    }

    /**
     * Get clicked subscribers for specified email id
     * @param $injectionIds
     * @return mixed
     */
    public static function getClickedSubscribers($emailId)
    {
        return static::with(['email_clicks' => function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            })->select(['email_clicks.target_link', 'email_clicks.hits']);
        }])->whereHas('email_injections', function($q) use ( $emailId ) {
            $q->where('email_id', $emailId);
        })->whereHas('email_clicks', function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            });
        })->orderBy('subscribers.first_name', 'asc')->paginate(100);
    }

    /**
     * Get undelivered subscribers for specified email id
     * @param $emailId
     * @return mixed
     */
    public static function getNotDeliveredSubscribers($emailId)
    {
        return static::whereHas('email_injections', function($q) use ( $emailId ) {
            $q->where('email_id', $emailId);
        })->whereDoesntHave('email_deliveries', function($query) use ( $emailId ) {
            $query->whereHas('email_injection', function($q) use ( $emailId ) {
                $q->where('email_id', $emailId);
            });
        })->orderBy('subscribers.first_name', 'asc')->paginate(100);
    }
}

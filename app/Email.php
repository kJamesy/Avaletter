<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class Email extends Model
{
    use Searchable;

    /**
     * Database table
     * @var string
     */
    protected $table = 'emails';

    /**
     * Validation rules
     * @var array
     */
    public static $rules = [
        'subscribers' => 'required_without:mailing_lists',
        'mailing_lists' => 'required_without:subscribers',
        'email_edition_id' => 'required|numeric|min:1',
        'subject' => 'required|max:255',
        'body' => 'required'
    ];

    /**
     * Inverse of One to Many User to Email relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Inverse of One to Many EmailEdition to Email relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function email_edition()
    {
        return $this->belongsTo(EmailEdition::class);
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
     * An email has many email deliveries through email injections
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function email_deliveries()
    {
        return $this->hasManyThrough(EmailDelivery::class, EmailInjection::class);
    }

    /**
     * An email has many email opens through email injections
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function email_opens()
    {
        return $this->hasManyThrough(EmailOpen::class, EmailInjection::class);
    }

    /**
     * An email has many email clicks through email injections
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function email_clicks()
    {
        return $this->hasManyThrough(EmailClick::class, EmailInjection::class);
    }

    /**
     * Get specified email
     * @param $id
     * @param int $deleted
     * @return mixed
     */
    public static function getEmail($id, $deleted = 0)
    {
        return static::with('email_edition:id,edition')
            ->with('user:id,first_name,last_name')
            ->where('is_deleted', $deleted)
            ->where('is_draft', 0)
            ->find($id);
    }

    /**
     * Get specified email draft
     * @param $id
     * @param int $deleted
     * @return mixed
     */
    public static function getEmailDraft($id, $deleted = 0)
    {
        return static::with('email_edition:id,edition')
            ->with('user:id,first_name,last_name')
            ->where('is_deleted', $deleted)
            ->where('is_draft', 1)
            ->find($id);
    }

    /**
     * Get emails
     * @param int $draft
     * @param int $deleted
     * @param string $orderBy
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public static function getEmails($draft = 0, $deleted = 0, $orderBy = 'created_at', $order = 'desc', $paginate = 1000)
    {
        return static::with('email_edition:id,edition')
            ->with('user:id,first_name,last_name')
            ->withCount('email_injections')
            ->where('is_draft', $draft)
            ->where('is_deleted', $deleted)
            ->orderBy($orderBy, $order)
            ->paginate($paginate);
    }

    /**
     * Get search results - we need a more complex 'where' here but scout can't help us. So we will need to do this in two steps
     * @param $search
     * @param int $draft
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSearchResults($search, $draft = 0)
    {
        return static::search($search)->where('is_draft', $draft)->get();
    }

    /**
     * Step two of search - fetch results from the database
     * @param array $ids
     * @param int $deleted
     * @param int $paginate
     * @return mixed
     */
    public static function fetchSearchedResults($ids = [], $deleted = 0, $paginate = 1000)
    {
        return static::with('email_edition:id,edition')
            ->with('user:id,first_name,last_name')
            ->withCount('email_injections')
            ->where('is_deleted', $deleted)
            ->whereIn('id', (array) $ids)
            ->paginate($paginate);
    }

    /**
     * Get email by id
     * @param $id
     * @return mixed
     */
    public static function getEmailById($id)
    {
        return static::find($id);
    }

    /**
     * Get the specified sent email
     * @param $id
     * @return mixed
     */
    public static function getSentEmail($id)
    {
        return static::with('email_edition:id,edition')
            ->with('user:id,first_name,last_name')
            ->withCount('email_injections')
            ->withCount('email_deliveries')
            ->withCount('email_opens')
            ->selectRaw("(SELECT COUNT(DISTINCT `email_injection_id`) FROM `email_clicks` INNER JOIN `email_injections` ON `email_injections`.`id` = `email_clicks`.`email_injection_id` WHERE `emails`.`id` = `email_injections`.`email_id`) AS `email_clicks_count`")
            ->where('send_success', 1)
            ->whereNotNull('sent_at')
            ->find($id);
    }

    /**
     * Get email with general stats (injection, deliveries, opens)
     * @param $id
     * @return mixed
     */
    public static function getEmailWithGeneralStats($id)
    {
        return static::withCount('email_injections')
            ->withCount('email_deliveries')
            ->withCount('email_opens')
            ->selectRaw("(SELECT COUNT(DISTINCT `email_injection_id`) FROM `email_clicks` INNER JOIN `email_injections` ON `email_injections`.`id` = `email_clicks`.`email_injection_id` WHERE `emails`.`id` = `email_injections`.`email_id`) AS `email_clicks_count`")
            ->where('is_deleted', 0)
            ->where('is_draft', 0)
            ->where('send_success', 1)
            ->whereNotNull('sent_at')
            ->find($id);
    }

    /**
     * Get email deliveries stats
     * @param $id
     * @return mixed
     */
    public static function getEmailDeliveriesStats($id)
    {
        return static::select('id')
            ->withCount('email_injections')
            ->withCount('email_deliveries')
            ->where('is_deleted', 0)
            ->where('is_draft', 0)
            ->where('send_success', 1)
            ->whereNotNull('sent_at')
            ->find($id);
    }

    /**
     * Get email opens stats
     * @param $id
     * @return mixed
     */
    public static function getEmailOpensStats($id)
    {
        return static::select('id')
            ->withCount('email_injections')
            ->withCount('email_opens')
            ->where('is_deleted', 0)
            ->where('is_draft', 0)
            ->where('send_success', 1)
            ->whereNotNull('sent_at')
            ->find($id);
    }

    /**
     * Get email clicks stats
     * @param $id
     * @return mixed
     */
    public static function getEmailClicksStats($id)
    {
        return static::select('id')
            ->withCount('email_injections')
            ->selectRaw("(SELECT COUNT(DISTINCT `email_injection_id`) FROM `email_clicks` INNER JOIN `email_injections` ON `email_injections`.`id` = `email_clicks`.`email_injection_id` WHERE `emails`.`id` = `email_injections`.`email_id`) AS `email_clicks_count`")
            ->where('is_deleted', 0)
            ->where('is_draft', 0)
            ->where('send_success', 1)
            ->whereNotNull('sent_at')
            ->find($id);
    }

    /**
     * Get email opens countries stats
     * @param $id
     * @param int $limit
     * @return mixed
     */
    public static function getEmailCountriesStats($id, $limit = 15)
    {
        return static::join('email_injections', 'email_injections.email_id', '=', 'emails.id')
            ->join('email_opens', 'email_opens.email_injection_id', '=', 'email_injections.id')
            ->select('email_opens.country AS country', DB::raw('COUNT(email_opens.country) AS country_count'))
            ->where('emails.id', $id)
            ->where('is_deleted', 0)
            ->where('is_draft', 0)
            ->where('send_success', 1)
            ->whereNotNull('sent_at')
            ->groupBy('email_opens.country')
            ->orderBy('country_count', 'desc')
            ->limit($limit)
            ->get();
    }

}

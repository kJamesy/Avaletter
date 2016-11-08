<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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
        return $this->belongsTo('App\User');
    }

    /**
     * Inverse of One to Many EmailEdition to Email relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function email_edition()
    {
        return $this->belongsTo('App\EmailEdition');
    }

    /**
     * Get specified email
     * @param $id
     * @param int $deleted
     * @return mixed
     */
    public static function getEmail($id, $deleted = 0)
    {
        return static::with('email_edition')->with('user')->where('is_deleted', $deleted)->where('is_draft', 0)->find($id);
    }

    /**
     * Get specified email draft
     * @param $id
     * @param int $deleted
     * @return mixed
     */
    public static function getEmailDraft($id, $deleted = 0)
    {
        return static::with('email_edition')->with('user')->where('is_deleted', $deleted)->where('is_draft', 1)->find($id);
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
        return static::with('email_edition')->with('user')->where('is_draft', $draft)->where('is_deleted', $deleted)->orderBy($orderBy, $order)->paginate($paginate);
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
        return static::with('email_edition')->with('user')->where('is_deleted', $deleted)->whereIn('id', (array) $ids)->paginate($paginate);
    }

}

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
     * @return \Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function getEmail($id)
    {
        return static::with('email_edition')->with('user')->find($id);
    }

    /**
     * Get emails
     * @param string $orderBy
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public static function getEmails($orderBy = 'created_at', $order = 'desc', $paginate = 1000)
    {
        return static::with('email_edition')->with('user')->orderBy($orderBy, $order)->paginate($paginate);
    }

    /**
     * Get search results
     * @param $search
     * @param int $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getSearchResults($search, $paginate = 1000)
    {
        return static::search($search)->paginate($paginate);
    }

}

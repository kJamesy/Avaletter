<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class EmailEdition extends Model
{
    use Searchable;

    /**
     * Database table
     * @var string
     */
    protected $table = 'email_editions';

    /**
     * Validation rules
     * @var array
     */
    public static $rules = ['edition' => 'required|unique:email_editions|max:255'];

    /**
     * One to Many Edition to Email relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emails()
    {
        return $this->hasMany('App\Email');
    }

    /**
     * Get specified email edition
     * @param $id
     * @return mixed
     */
    public static function getEdition($id)
    {
        return static::find($id);
    }

    /**
     * Get editions
     * @param string $orderBy
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public static function getEditions($orderBy = 'created_at', $order = 'desc', $paginate = 1000)
    {
        return static::withCount('emails')->orderBy($orderBy, $order)->paginate($paginate);
    }

    /**
     * Get specified editions
     * @param $ids
     * @param string $orderBy
     * @param string $order
     * @return mixed
     */
    public static function getSpecifiedEditions($ids, $orderBy = 'created_at', $order = 'asc')
    {
        return static::whereIn('id', $ids)->orderBy($orderBy, $order)->get();
    }

    /**
     * Get all editions
     * @return mixed
     */
    public static function getEditionsList()
    {
        return static::orderBy('edition')->get(['id', 'edition']);
    }

    /**
     * Get search results
     * @param $search
     * @param int $paginate
     * @return mixed
     */
    public static function getSearchResults($search, $paginate = 1000)
    {
        return static::withCount('emails')->search($search)->paginate($paginate);
    }
}

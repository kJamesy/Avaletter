<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class EmailTemplate extends Model
{
    use Searchable;

    /**
     * Database table
     * @var string
     */
    protected $table = 'email_templates';

    /**
     * Validation rules
     * @var array
     */
    public static $rules = ['name' => 'required|unique:email_templates|max:255', 'content' => 'required'];

    /**
     * Get specified template
     * @param $id
     * @return mixed
     */
    public static function getTemplate($id)
    {
        return static::find($id);
    }

    /**
     * Get templates
     * @param string $orderBy
     * @param string $order
     * @param int $paginate
     * @return mixed
     */
    public static function getTemplates($orderBy = 'created_at', $order = 'desc', $paginate = 1000)
    {
        return static::orderBy($orderBy, $order)->paginate($paginate);
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

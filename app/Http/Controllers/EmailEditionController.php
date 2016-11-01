<?php

namespace App\Http\Controllers;

use App\EmailEdition;
use App\User;
use Illuminate\Http\Request;

class EmailEditionController extends Controller
{

    public $rules;
    public $paginate;
    public $orderCriteria;
    public $orderByFields;

    /**
     * EmailEditionController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->rules = EmailEdition::$rules;
        $this->paginate = 25;
        $this->orderByFields = ['edition', 'emails_count', 'created_at', 'updated_at'];
        $this->orderCriteria = ['asc', 'desc'];
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function index(Request $request)
    {
        if ( ! $request->ajax() ) {
            $settings = ['email_editions_order_by' => 'updated_at', 'email_editions_order' => 'desc', 'email_editions_paginate' => $this->paginate];

            if ( $user = $request->user() ) {
                if ( cache()->has('user_' . $user->id . '_email_editions_order_by') ) {
                    $settings['email_editions_order_by'] = cache('user_' . $user->id . '_email_editions_order_by');
                    $settings['email_editions_order'] = cache('user_' . $user->id . '_email_editions_order');
                    $settings['email_editions_paginate'] = cache('user_' . $user->id . '_email_editions_paginate');
                }
                else {
                    User::cacheSettings($user->id,
                        ['email_editions_order_by', 'email_editions_order', 'email_editions_paginate'],
                        [$settings['email_editions_order_by'], $settings['email_editions_order'], $settings['email_editions_paginate']]);
                }
            }

            return view('email_edition_index')->with(compact('settings'));
        }
        else {
            $orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : 'updated_at';
            $order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : 'desc';
            $paginate = (int) $request->perPage ?: $this->paginate;

            if ( $user = $request->user() )
                User::cacheSettings($user->id, ['email_editions_order_by', 'email_editions_order', 'email_editions_paginate'], [$orderBy, $order, $paginate]);

            return EmailEdition::getEditions($orderBy, $order, $paginate);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return EmailEdition
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        $email_edition = new EmailEdition();
        $email_edition->edition = trim($request->edition);
        $email_edition->save();

        return $email_edition;
    }

    /**
     * Display the specified resource.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ( $email_edition = EmailEdition::getEdition( (int) $id ) )
            return $email_edition;
        else
            return response()->json(['error' => 'Email edition does not exist'], 404);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if ( $email_edition = EmailEdition::getEdition( (int) $id) ) {

            if ( strtolower($email_edition->edition) != strtolower(trim($request->edition)) ) {
                $this->validate($request, $this->rules);

                $email_edition->edition = trim($request->edition);
                $email_edition->save();
            }

            return $email_edition;
        }
        else
            return response()->json(['error' => 'Email edition does not exist'], 404);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if ( $email_edition = EmailEdition::getEdition( (int) $id ) ) {
            $email_edition->delete();
            return response()->json(['success' => 'Email edition permanently deleted']);
        }
        else
            return response()->json(['error' => 'Email edition does not exist'], 404);
    }
}

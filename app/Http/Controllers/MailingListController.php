<?php

namespace App\Http\Controllers;

use App\MailingList;
use App\User;
use Illuminate\Http\Request;

class MailingListController extends Controller
{
    public $rules;
    public $paginate;
    public $orderCriteria;
    public $orderByFields;

    /**
     * MailingListController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->rules = MailingList::$rules;
        $this->paginate = 25;
        $this->orderByFields = ['name', 'subscribers_count', 'created_at', 'updated_at'];
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
            $settings = ['mailing_lists_order_by' => 'updated_at', 'mailing_lists_order' => 'desc', 'mailing_lists_paginate' => $this->paginate];

            if ( $user = $request->user() ) {
                if ( cache()->has('user_' . $user->id . '_mailing_lists_order_by') ) {
                    $settings['mailing_lists_order_by'] = cache('user_' . $user->id . '_mailing_lists_order_by');
                    $settings['mailing_lists_order'] = cache('user_' . $user->id . '_mailing_lists_order');
                    $settings['mailing_lists_paginate'] = cache('user_' . $user->id . '_mailing_lists_paginate');
                }
                else {
                    User::cacheSettings($user->id,
                        ['mailing_lists_order_by', 'mailing_lists_order', 'mailing_lists_paginate'],
                        [$settings['mailing_lists_order_by'], $settings['mailing_lists_order'], $settings['mailing_lists_paginate']]);
                }
            }

            return view('mailing_list_index')->with(compact('settings'));
        }
        else {
            $orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : 'updated_at';
            $order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : 'desc';
            $paginate = (int) $request->perPage ?: $this->paginate;

            if ( $user = $request->user() )
                User::cacheSettings($user->id, ['mailing_lists_order_by', 'mailing_lists_order', 'mailing_lists_paginate'], [$orderBy, $order, $paginate]);

            return MailingList::getMailingLists($orderBy, $order, $paginate);
        }
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return MailingList
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        $mList = new MailingList();
        $mList->name = trim($request->name);
        $mList->save();

        return $mList;
    }

    /**
     * Display the specified resource.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ( $mList = MailingList::getMailingList(( (int) $id) ) )
            return $mList;
        else
            return response()->json(['error' => 'Mailing list does not exist'], 404);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if ( $mList = MailingList::getMailingList(( (int) $id) ) ) {

            if ( $mList->name != trim($request->name) ) {
                $this->validate($request, $this->rules);

                $mList->name = trim($request->name);
                $mList->save();
            }

            return $mList;
        }
        else
            return response()->json(['error' => 'Mailing list does not exist'], 404);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
       if ( $mList = MailingList::getMailingList(( (int) $id) ) ) {
           $mList->delete();
           return response()->json(['success' => 'Mailing list successfully permanently deleted']);
       }
       else
           return response()->json(['error' => 'Mailing list does not exist'], 404);
    }
}

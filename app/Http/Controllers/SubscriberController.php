<?php

namespace App\Http\Controllers;

use App\MailingList;
use App\Subscriber;
use App\User;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public $rules;
    public $paginate;
    public $orderCriteria;
    public $orderByFields;

    /**
     * SubscriberController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->rules = Subscriber::$rules;
        $this->paginate = 25;
        $this->orderByFields = ['first_name', 'last_name', 'email', 'active', 'created_at', 'updated_at'];
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
            $settings = ['subscribers_order_by' => 'updated_at', 'subscribers_order' => 'desc', 'subscribers_paginate' => $this->paginate];

            if ( $user = $request->user() ) {
                if ( cache()->has('user_' . $user->id . '_subscribers_order_by') ) {
                    $settings['subscribers_order_by'] = cache('user_' . $user->id . '_subscribers_order_by');
                    $settings['subscribers_order'] = cache('user_' . $user->id . '_subscribers_order');
                    $settings['subscribers_paginate'] = cache('user_' . $user->id . '_subscribers_paginate');
                }
                else {
                    User::cacheSettings($user->id,
                        ['subscribers_order_by', 'subscribers_order', 'subscribers_paginate'],
                        [$settings['subscribers_order_by'], $settings['subscribers_order'], $settings['subscribers_paginate']]);
                }
            }

            return view('subscriber_index')->with(compact('settings'));

        }
        else {
            $orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : 'updated_at';
            $order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : 'desc';
            $paginate = (int) $request->perPage ?: $this->paginate;

            if ( $user = $request->user() )
                User::cacheSettings($user->id, ['subscribers_order_by', 'subscribers_order', 'subscribers_paginate'], [$orderBy, $order, $paginate]);

            $getDeleted = $request->trash ? 1 : 0;
            $getInMailingList = (int) $request->mailingList ?: 0;

            $mailing_list = $getInMailingList ? MailingList::getMailingList($getInMailingList) : null;
            $mailing_lists = MailingList::getMailingListsList();
            $subscribers = Subscriber::getSubscribers($orderBy, $order, $paginate, $getInMailingList, $getDeleted);

            return response()->json(compact('mailing_list', 'mailing_lists', 'subscribers'));
        }
    }

    /**
     * Get mailing lists options for creating new subscriber
     * @return mixed
     */
    public function create()
    {
        return MailingList::getMailingListsList();
    }

    /**
     * Store a newly created resource in storage
     * @param Request $request
     * @return Subscriber
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        $subscriber = new Subscriber();
        $subscriber->first_name = trim($request->first_name);
        $subscriber->last_name = trim($request->last_name);
        $subscriber->email = strtolower(trim($request->email));
        $subscriber->active = $request->active ? 1 : 0;
        $subscriber->save();

        if ( $request->has('mailing_lists') && is_array($request->mailing_lists) && $request->mailing_lists )
            $subscriber->mailing_lists()->attach($request->mailing_lists);

        return $subscriber;
    }

    /**
     * Display the specified resource.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ( $subscriber = Subscriber::getActiveSubscriber( (int) $id) ) {
            $mLists = MailingList::getMailingListsList();
            return response()->json(compact('subscriber', 'mLists'));
        }
        else
            return response()->json(['error' => 'Subscriber does not exist'], 404);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if ( $subscriber = Subscriber::getActiveSubscriber( (int) $id) ) {
            $rules = $this->rules;
            if ( strtolower($subscriber->email) == strtolower(trim($request->email)) )
                $rules['email'] = 'required|email|max:255';

            $this->validate($request, $rules);

            $subscriber->first_name = trim($request->first_name);
            $subscriber->last_name = trim($request->last_name);
            $subscriber->email = strtolower(trim($request->email));
            $subscriber->active = $request->active ? 1 : 0;
            $subscriber->save();

            if ( $request->has('mailing_lists') && is_array($request->mailing_lists) && $request->mailing_lists ) {
                $subscriber->mailing_lists()->sync($request->mailing_lists);
                $subscriber->touch();
            }

            return response()->json(compact('subscriber'));
        }
        else
            return response()->json(['error' => 'Subscriber does not exist'], 404);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if ( $subscriber = Subscriber::getSubscriber( (int) $id) ) {
            if ( $request->has('destroy') && $request->destroy ) {
                $subscriber->delete();
                return response()->json(['success' => 'Subscriber permanently deleted']);
            }
            else {
                $subscriber->is_deleted = 1;
                $subscriber->save();
                return response()->json(['success' => 'Subscriber successfully moved to trash']);
            }
        }
        else
            return response()->json(['error' => 'Subscriber does not exist'], 404);
    }

    /**
     * Perform specified action on provided resource
     * @param Request $request
     * @param $update
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickUpdate(Request $request, $update)
    {
        if ( count($request->subscribers) ) {
            try {
                if ( strtolower($update) == 'destroy' ) {
                    Subscriber::whereIn('id', $request->subscribers)->delete();

                    return response()->json(['success' => count($request->subscribers) . ' subscribers successfully permanently deleted']);
                }
                else {
                    $subscribers = Subscriber::whereIn('id', $request->subscribers)->get();

                    if ($subscribers) {
                        foreach ($subscribers as $subscriber) {
                            switch (strtolower($update)) {
                                case 'activate':
                                    $subscriber->active = 1;
                                    break;
                                case 'deactivate':
                                    $subscriber->active = 0;
                                    break;
                                case 'delete':
                                    $subscriber->is_deleted = 1;
                                    break;
                                case 'restore':
                                    $subscriber->is_deleted = 0;
                                    break;
                            }
                            $subscriber->save();
                        }
                        $feedback = ($update == 'delete') ? 'moved to trash' : $update . 'd';
                        return response()->json(['success' => count($subscribers) . ' subscribers successfully ' . $feedback]);
                    } else
                        return response()->json(['error' => 'Subscribers do not exist'], 404);
                }
            } catch(\Exception $e) {
                return response()->json(['error' => 'A server error occurred.'], 500);
            };
        }
        else
            return response()->json(['error' => 'No subscribers received'], 500);
    }
}

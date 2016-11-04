<?php

namespace App\Http\Controllers;

use App\Email;
use App\EmailEdition;
use App\Jobs\SendNewsletter;
use App\Mail\Newsletter;
use App\MailingList;
use App\Subscriber;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public $rules;
    public $paginate;
    public $orderByFields;
    public $orderCriteria;

    /**
     * EmailController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->rules = Email::$rules;
        $this->paginate = 25;
        $this->orderByFields = ['subject', 'from', 'sent_at', 'created_at', 'updated_at'];
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
            $settings = ['emails_order_by' => 'updated_at', 'emails_order' => 'desc', 'emails_paginate' => $this->paginate];

            if ( $user = $request->user() ) {
                if ( cache()->has('user_' . $user->id . '_emails_order_by') ) {
                    $settings['emails_order_by'] = cache('user_' . $user->id . '_emails_order_by');
                    $settings['emails_order'] = cache('user_' . $user->id . '_emails_order');
                    $settings['emails_paginate'] = cache('user_' . $user->id . '_emails_paginate');
                }
                else {
                    User::cacheSettings($user->id,
                        ['emails_order_by', 'emails_order', 'emails_paginate'],
                        [$settings['emails_order_by'], $settings['emails_order'], $settings['emails_paginate']]);
                }
            }

            return view('email_index')->with(compact('settings'));
        }
        else {
            $orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : 'updated_at';
            $order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : 'desc';
            $paginate = (int) $request->perPage ?: $this->paginate;

            if ( $user = $request->user() )
                User::cacheSettings($user->id, ['emails_order_by', 'emails_order', 'emails_paginate'], [$orderBy, $order, $paginate]);

            $search = strtolower($request->search);

            if ( $search )
                $emails = Email::getSearchResults($search, $paginate);
            else
                $emails = Email::getEmails($orderBy, $order, $paginate);

            return response()->json(compact('emails'));
        }
    }

    /**
     * Get various options for creating a new email
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $mailing_lists = MailingList::getMailingListsList();
        $subscribers = Subscriber::getEmailableSubscribersList();
        $email_editions = EmailEdition::getEditionsList();

        return response()->json(compact('mailing_lists', 'subscribers', 'email_editions'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Email|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ( $user = $request->user() ) {
            $this->validate($request, $this->rules);

            $email = new Email();
            $email->user_id = $user->id;
            $email->email_edition_id = (int) $request->email_edition_id;
            $email->from = "$user->first_name $user->last_name <$user->email>";
            $email->subject = trim($request->subject);
            $email->body = $request->body;
            $email->is_draft = $request->is_draft;
            $email->sent_at = Carbon::now();
            $email->save();

            $subscribers = Subscriber::whereIn('id', [1,2,3,4])->get();

            $job = (new SendNewsletter($email, $subscribers)); //->delay(Carbon::now()->addMinutes(2));
            dispatch($job);

//            $newsletter = new Newsletter($email, $subscribers);
//            $newsletter->fireEmail();

            return $email;
        }
        else
            return response()->json(['authentication' => 'User not authenticated.'], 500);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

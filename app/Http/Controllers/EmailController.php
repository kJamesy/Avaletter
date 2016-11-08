<?php

namespace App\Http\Controllers;

use App\AvaHelper\SimpleHTMLToPDF;
use App\Email;
use App\EmailEdition;
use App\Jobs\SendNewsletter;
use App\MailingList;
use App\Subscriber;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
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
            $draft = (int) $request->draft ?: 0;
            $deleted = (int) $request->deleted ?: 0;

            if ( $user = $request->user() )
                User::cacheSettings($user->id, ['emails_order_by', 'emails_order', 'emails_paginate'], [$orderBy, $order, $paginate]);

            $search = strtolower($request->search);

            if ( $search ) {
                $premEmails = Email::getSearchResults($search, $draft);
                $premEmailsIds = [];

                foreach( $premEmails as $email )
                    $premEmailsIds[] = $email->id;

                $emails = Email::fetchSearchedResults($premEmailsIds, $deleted, $paginate);
            }
            else
                $emails = Email::getEmails($draft, $deleted, $orderBy, $order, $paginate);

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ( $user = $request->user() ) {
            $rules = $this->rules;

            if ( $request->is_draft )
                $rules = array_diff_key($rules, ['mailing_lists' => '', 'subscribers' => '']);

            $this->validate($request, $rules);

            $send_at = Carbon::now()->addMinutes(5);
            if ( $request->send_at && array_key_exists('time', $request->send_at) && strlen($request->send_at['time']) ) {
                $rawTime = $request->send_at['time'];
                $userInputDt = Carbon::createFromFormat('Y-m-d H:i', $rawTime);

                if ( ! $userInputDt->isPast() )
                    $send_at = $userInputDt;
            }

            $email = new Email();
            $email->user_id = $user->id;
            $email->email_edition_id = (int) $request->email_edition_id;
            $email->from = "$user->first_name $user->last_name <$user->email>";
            $email->subject = trim($request->subject);
            $email->body = $request->body;
            $email->is_draft = $request->is_draft;
            $email->sent_at = $request->is_draft ? null : $send_at;
            $email->save();

            if ( ! $request->is_draft ) {
                $scheduled = $send_at->isPast() ? Carbon::now()->addMinutes(5) : $send_at; //Doesn't hurt to check again
                $selectedSubs = new Collection();
                $selectedMListSubs = new Collection();

                if ( $request->subscribers )
                    $selectedSubs = Subscriber::getEmailableSubscibersByIds($request->subscribers, false);

                if ( $request->mailing_lists ) {
                    $except = $request->subscribers ? Subscriber::getEmailableSubscibersByIds($request->subscribers) : [];
                    $selectedMListSubs = Subscriber::getEmailableSubscribersByMLists($request->mailing_lists, $except);
                }

                $emailableSubs = $selectedSubs->merge($selectedMListSubs);

                $job = (new SendNewsletter($email, $emailableSubs))->delay($scheduled);
                dispatch($job);

                $message = "Your email has been scheduled for " . $scheduled->diffForHumans() . ". Please do not edit associated email/subscribers in the meantime.";
            }
            else
                $message = "Your email draft has been saved.";

            return response()->json(compact('message'));
        }
        else
            return response()->json(['authentication' => 'User not authenticated.'], 500);
    }

    /**
     * Display the specified resource.
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $email = ( $request->has('sent') && $request->sent ) ? Email::getEmail( (int) $id ) : Email::getEmailDraft( (int) $id );

        if ( $email ) {
            $mailing_lists = MailingList::getMailingListsList();
            $subscribers = Subscriber::getEmailableSubscribersList();
            $email_editions = EmailEdition::getEditionsList();

            return response()->json(compact('email', 'mailing_lists', 'subscribers', 'email_editions'));
        }
        else
            return response()->json(['error' => 'Email draft does not exist'], 404);
    }

    /**
     * Update the specified resource in storage
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $email = Email::getEmailDraft( (int) $id );
        $user = $request->user();

        if ( $email && $user ) {
            $rules = $this->rules;

            if ( $request->is_draft )
                $rules = array_diff_key($rules, ['mailing_lists' => '', 'subscribers' => '']);

            $this->validate($request, $rules);

            $send_at = Carbon::now()->addMinutes(5);
            if ( $request->send_at && array_key_exists('time', $request->send_at) && strlen($request->send_at['time']) ) {
                $rawTime = $request->send_at['time'];
                $userInputDt = Carbon::createFromFormat('Y-m-d H:i', $rawTime);

                if ( ! $userInputDt->isPast() )
                    $send_at = $userInputDt;
            }

            $email->user_id = $user->id;
            $email->email_edition_id = (int) $request->email_edition_id;
            $email->from = "$user->first_name $user->last_name <$user->email>";
            $email->subject = trim($request->subject);
            $email->body = $request->body;
            $email->is_draft = $request->is_draft;
            $email->sent_at = $request->is_draft ? null : $send_at;
            $email->save();

            if ( $request->is_draft )
                return response()->json(['saved' => 'Your email draft has been updated.']);
            else {
                $scheduled = $send_at->isPast() ? Carbon::now()->addMinutes(5) : $send_at; //Doesn't hurt to check again
                $selectedSubs = new Collection();
                $selectedMListSubs = new Collection();

                if ( $request->subscribers )
                    $selectedSubs = Subscriber::getEmailableSubscibersByIds($request->subscribers, false);

                if ( $request->mailing_lists ) {
                    $except = $request->subscribers ? Subscriber::getEmailableSubscibersByIds($request->subscribers) : [];
                    $selectedMListSubs = Subscriber::getEmailableSubscribersByMLists($request->mailing_lists, $except);
                }

                $emailableSubs = $selectedSubs->merge($selectedMListSubs);

                $job = (new SendNewsletter($email, $emailableSubs))->delay($scheduled);
                dispatch($job);

                $message = "Your email has been scheduled for " . $scheduled->diffForHumans() . ". Please do not edit associated email/subscribers in the meantime.";

                return response()->json(['queued' => $message]);
            }
        }
        else
            return response()->json(['error' => 'Email draft does not exist'], 404);
    }

    /**
     *Perform specified action on provided resources
     * @param Request $request
     * @param $update
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickUpdate(Request $request, $update)
    {
        if ( count($request->emails) ) {
            try {

                $emails = Email::whereIn('id', (array) $request->emails)->get();

                if ( $emails ) {
                    $update = ( ( $request->has('destroy') && $request->destroy ) || ( $request->has('draft') && $request->draft ) ) ? 'delete' : 'trash';

                    foreach ($emails as $email) {
                        switch ($update) {
                            case 'delete':
                                $email->delete();
                                break;
                            case 'trash':
                                $email->is_deleted = 1;
                                $email->save();
                                break;
                        }
                    }

                    $feedback = ( $update == 'delete' ) ? 'item(s) permanently deleted' : 'item(s) moved to trash';
                    return response()->json(['success' => count($emails) . " $feedback"]);
                }
                else
                    return response()->json(['error' => 'Emails do not exist'], 404);

            }
            catch(\Exception $e) {
                return response()->json(['error' => 'A server error occurred.'], 500);
            }
        }
        else
            return response()->json(['error' => 'No emails received'], 500);
    }

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        if ( $email = Email::find( (int) $id ) ) {
            if ( ( $request->has('destroy') && $request->destroy ) || $email->is_draft || $email->is_deleted ) {
                $email->delete();
                return response()->json(['success' => 'Email permanently deleted']);
            }
            else {
                $email->is_deleted = 1;
                $email->save();
                return response()->json(['success' => 'Email moved to trash']);
            }
        }
        else
            return response()->json(['error' => 'Email does not exist'], 404);
    }


    /**
     * Export to PDF
     * @param Request $request
     */
    public function export(Request $request)
    {
        $id = (int) $request->id;
        $link = route('emails.display', ['id' => $id]);
        $pdf = new SimpleHTMLToPDF();
        $pdf->display($link);
    }

    /**
     * Display the email externally
     * @param $id
     */
    public function externalDisplay($id)
    {
        if ( $email = Email::find( (int) $id ) )
            echo $email->body;
        else
            echo 'No email found';
    }

}

<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use App\User;
use Illuminate\Http\Request;


class EmailTemplateController extends Controller
{

    public $rules;
    public $paginate;
    public $orderByFields;
    public $orderCriteria;

    /**
     * TemplateController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->rules = EmailTemplate::$rules;
        $this->paginate = 25;
        $this->orderByFields = ['name', 'last_edited_by', 'created_at', 'updated_at'];
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
            $settings = ['templates_order_by' => 'updated_at', 'templates_order' => 'desc', 'templates_paginate' => $this->paginate];

            if ( $user = $request->user() ) {
                if ( cache()->has('user_' . $user->id . '_templates_order_by') ) {
                    $settings['templates_order_by'] = cache('user_' . $user->id . '_templates_order_by');
                    $settings['templates_order'] = cache('user_' . $user->id . '_templates_order');
                    $settings['templates_paginate'] = cache('user_' . $user->id . '_templates_paginate');
                }
                else {
                    User::cacheSettings($user->id,
                        ['templates_order_by', 'templates_order', 'templates_paginate'],
                        [$settings['templates_order_by'], $settings['templates_order'], $settings['templates_paginate']]);
                }
            }

            return view('email_template_index')->with(compact('settings'));
        }
        else {
            $orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : 'updated_at';
            $order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : 'desc';
            $paginate = (int) $request->perPage ?: $this->paginate;

            if ( $user = $request->user() )
                User::cacheSettings($user->id, ['templates_order_by', 'templates_order', 'templates_paginate'], [$orderBy, $order, $paginate]);

            $search = strtolower($request->search);

            if ( $search )
                $emailTemplates = EmailTemplate::getSearchResults($search, $paginate);
            else
                $emailTemplates = EmailTemplate::getTemplates($orderBy, $order, $paginate);

            return response()->json(compact('emailTemplates'));
        }
    }

    /**
     * Store a newly created resource in storage
     * @param Request $request
     * @return EmailTemplate|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ( $user = $request->user() ) {
            $this->validate($request, $this->rules);

            $email_template = new EmailTemplate();
            $email_template->last_edited_by = "$user->first_name $user->last_name";
            $email_template->name = trim($request->name);
            $email_template->content = $request->get('content');
            $email_template->save();

            return $email_template;
        }
        else {
            return response()->json(['authentication' => 'User not authenticated.'], 500);
        }
    }

    /**
     * Display the specified resource.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ( $email_template = EmailTemplate::getTemplate( (int) $id) )
            return response()->json(compact('email_template'));
        else
            return response()->json(['error' => 'Email template does not exist'], 404);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $email_template = EmailTemplate::getTemplate( (int) $id);
        $user = $request->user();

        if ( $email_template && $user ) {
            $rules = $this->rules;
            if ( strtolower($email_template->name) == strtolower(trim($request->name)) )
                $rules['name'] = 'required|max:255';

            $this->validate($request, $rules);

            $email_template->last_edited_by = "$user->first_name $user->last_name";
            $email_template->name = trim($request->name);
            $email_template->content = $request->get('content');
            $email_template->save();

            return response()->json(compact('email_template'));
        }
        else
            return response()->json(['error' => 'Email template does not exist'], 404);
    }

    /**
     * Perform specified action on provided resource
     * @param Request $request
     * @param $update
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickUpdate(Request $request, $update)
    {
        if ( count($request->email_templates) ) {
            try {
                $email_templates = EmailTemplate::whereIn('id', $request->email_templates)->get();

                if ($email_templates) {
                    foreach ($email_templates as $email_template) {
                        switch (strtolower($update)) {
                            case 'delete':
                                $email_template->delete();
                                break;
                        }
                    }

                    $feedback = 'deleted';
                    return response()->json(['success' => count($email_templates) . ' email templates ' . $feedback]);
                }
                else
                    return response()->json(['error' => 'Email templates do not exist'], 404);

            }
            catch(\Exception $e) {
                return response()->json(['error' => 'A server error occurred.'], 500);
            }
        }
        else
            return response()->json(['error' => 'No email templates received'], 500);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if ( $email_template = EmailTemplate::getTemplate( (int) $id) ) {
            $email_template->delete();
            return response()->json(['success' => 'Email template permanently deleted']);
        }
        else
            return response()->json(['error' => 'Email template does not exist'], 404);
    }


    /**
     * Export to PDF
     * @param Request $request
     */
    public function export(Request $request)
    {

        $id = (int) $request->id;

        if ( $email_template = EmailTemplate::getTemplate( (int) $id) ) {
            echo "Coming soon...";
        }
    }
}

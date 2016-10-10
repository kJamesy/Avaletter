<?php

namespace App\Http\Controllers;

use App\MailingList;
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
        $this->paginate = 1000;
        $this->orderByFields = ['name', 'created_at', 'updated_at'];
        $this->orderCriteria = ['asc', 'desc'];
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     */
    public function index(Request $request)
    {
        if ( ! $request->ajax() )
            return view('mailing_list_index');
        else {
            $orderBy = in_array(strtolower($request->orderBy), $this->orderByFields) ? strtolower($request->orderBy) : 'created_at';
            $order = in_array(strtolower($request->order), $this->orderCriteria) ? strtolower($request->order) : 'desc';
            $paginate = (int) $request->perPage ?: $this->paginate;

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
        if ( $mList = MailingList::find($id) )
            return $mList;
        else
            return response()->json(['error' => 'Mailing list does not exist']);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if ( $mList = MailingList::find($id) ) {

            if ( $mList->name != trim($request->name) ) {
                $this->validate($request, $this->rules);

                $mList->name = trim($request->name);
                $mList->save();
            }

            return $mList;
        }
        else
            return response()->json(['error' => 'Mailing list does not exist']);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
       if ( $mList = MailingList::find($id) ) {
           $mList->delete();
           return response()->json(['success' => 'Mailing list successfully deleted']);
       }
       else
           return response()->json(['error' => 'Mailing list does not exist']);
    }
}

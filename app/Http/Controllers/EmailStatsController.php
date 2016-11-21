<?php

namespace App\Http\Controllers;

use App\Email;
use App\Subscriber;
use Illuminate\Http\Request;

class EmailStatsController extends Controller
{
    /**
     * Get sent email
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSentEmail($id)
    {
        if ( $email = Email::getSentEmail( (int) $id ) ) {
            $email->url = route('emails.display', ['id' => $id]);
            return response()->json(compact('email'));
        }
        else
            return response()->json(['error' => 'Email does not exist'], 404);
    }

    /**
     * Get specified recipients for the specified sent email
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSentEmailRecipients(Request $request, $id)
    {
        $subscribers = null;

        switch( $request->statType ) {
            case 'injections':
                $subscribers = Subscriber::getInjectedSubscribers($id);
                break;
            case 'deliveries':
                $subscribers = Subscriber::getDeliveredSubscribers($id);
                break;
            case 'opens':
                $subscribers = Subscriber::getOpenedSubscribers($id);
                break;
            case 'clicks':
                $subscribers = Subscriber::getClickedSubscribers($id);
                break;
            case 'undelivered':
                $subscribers = Subscriber::getNotDeliveredSubscribers($id);
                break;
        }

        if ( $subscribers )
            return response()->json(compact('subscribers'));
        else
            return response()->json(['error' => 'Recipients not found'], 400);
    }

    /**
     * Get sent email general stats
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSentEmailGeneralStats($id)
    {
        if ( $email = Email::getEmailWithGeneralStats( (int) $id ) )
            return response()->json(compact('email'));
        else
            return response()->json(['error' => 'Email does not exist'], 404);
    }

    /**
     * Get specified stats for specified email
     * @param $id
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSentEmailStats($id, $type)
    {
        $email = null;

        switch( $type ) {
            case 'deliveries':
                $email = Email::getEmailDeliveriesStats($id);
                break;
            case 'opens':
                $email = Email::getEmailOpensStats($id);
                break;
            case 'clicks':
                $email = Email::getEmailClicksStats($id);
                break;
            case 'd_countries':
                $email = Email::getEmailCountriesStats($id);
                break;
        }

        if ( $email )
            return response()->json(compact('email'));
        else
            return response()->json(['error' => 'Statistics not found'], 404);
    }


}

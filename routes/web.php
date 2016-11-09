<?php
Route::get('lab', function() {
    $agent = new \Jenssegers\Agent\Agent();
//    $response = '[{"msys":{"message_event":{"ip_pool":"shared","message_id":"00059df022589746e863","template_version":"0","type":"injection","sending_ip":"54.244.48.140","routing_domain":"acw.uk.com","event_id":"102475407969004970","rcpt_tags":[],"campaign_id":"10","template_id":"template_102475407946750991","transmission_id":"102475407946750991","timestamp":"1478684829","subject":"Persist Webhook Response","rcpt_meta":[],"rcpt_to":"james@acw.uk.com","friendly_from":"james@ava.email-newsletter.info","msg_from":"msprvs1=17121tC8zTBY2=bounces-94949@sparkpostmail1.com","customer_id":"94949","msg_size":"28461","raw_rcpt_to":"james@acw.uk.com"}}},{"msys":{"message_event":{"type":"injection","rcpt_to":"ling@acw.uk.com","rcpt_meta":[],"ip_pool":"shared","message_id":"00059df022589746e963","event_id":"102475407969004971","rcpt_tags":[],"transmission_id":"102475407946750991","routing_domain":"acw.uk.com","friendly_from":"james@ava.email-newsletter.info","msg_size":"28425","template_id":"template_102475407946750991","customer_id":"94949","template_version":"0","msg_from":"msprvs1=17121tC8zTBY2=bounces-94949@sparkpostmail1.com","campaign_id":"10","timestamp":"1478684829","subject":"Persist Webhook Response","sending_ip":"52.38.191.220","raw_rcpt_to":"ling@acw.uk.com"}}}]';
//
//    foreach(json_decode($response) as $one) {
//        var_dump($one->msys->message_event);
//    }
//
//    $response2 = '[{"msys":{"message_event":{"queue_time":"34142","campaign_id":"10","ip_pool":"shared","msg_from":"msprvs1=17121tC8zTBY2=bounces-94949@sparkpostmail1.com","type":"delivery","customer_id":"94949","ip_address":"213.199.154.202","friendly_from":"james@ava.email-newsletter.info","rcpt_to":"ling@acw.uk.com","msg_size":"28425","event_id":"102475407969014536","rcpt_meta":[],"timestamp":"1478684863","sending_ip":"52.38.191.220","template_id":"template_102475407946750991","routing_domain":"acw.uk.com","num_retries":"0","message_id":"00059df022589746e963","subject":"Persist Webhook Response","delv_method":"esmtp","template_version":"0","transmission_id":"102475407946750991","rcpt_tags":[],"raw_rcpt_to":"ling@acw.uk.com"}}}]';
//
//    foreach(json_decode($response2) as $one) {
//        var_dump($one->msys->message_event);
//    }

//    $response3 = '[{"msys":{"message_event":{"num_retries":"0","template_id":"template_102475407946750991","timestamp":"1478684864","rcpt_meta":[],"event_id":"102475407969014682","message_id":"00059df022589746e863","delv_method":"esmtp","ip_address":"213.199.154.202","subject":"Persist Webhook Response","routing_domain":"acw.uk.com","friendly_from":"james@ava.email-newsletter.info","type":"delivery","rcpt_to":"james@acw.uk.com","customer_id":"94949","msg_size":"28461","transmission_id":"102475407946750991","sending_ip":"54.244.48.140","rcpt_tags":[],"campaign_id":"10","queue_time":"34848","msg_from":"msprvs1=17121tC8zTBY2=bounces-94949@sparkpostmail1.com","ip_pool":"shared","template_version":"0","raw_rcpt_to":"james@acw.uk.com"}}}]';
//
//    foreach(json_decode($response3) as $one) {
//        var_dump($one->msys->message_event);
//    }

//    $response4 = '[{"msys":{"track_event":{"transmission_id":"102475407946750991","rcpt_tags":[],"event_id":"30417853765723147","ip_address":"81.137.64.45","customer_id":"94949","rcpt_to":"james@acw.uk.com","template_id":"template_102475407946750991","template_version":"0","campaign_id":"10","type":"open","timestamp":"1478684919","delv_method":"esmtp","sending_ip":"54.244.48.140","message_id":"00059df022589746e863","user_agent":"Mozilla\/4.0 (compatible; MSIE 7.0; Windows NT 10.0; WOW64; Trident\/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; Microsoft Outlook 16.0.7369; ms-office; MSOffice 16)","ip_pool":"shared","rcpt_meta":[],"geo_ip":{"country":"GB","region":"L9","city":"Sheffield","latitude":53.3429,"longitude":-1.3495},"raw_rcpt_to":"james@acw.uk.com"}}}]';
//
//    $responseV = '[{"msys":{"track_event":{"delv_method":"esmtp","template_version":"0","campaign_id":"10","rcpt_tags":[],"user_agent":"Mozilla\/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko\/20100101 Firefox\/49.0","sending_ip":"54.244.48.140","ip_pool":"shared","event_id":"30417853768274318","rcpt_meta":[],"ip_address":"81.137.64.45","customer_id":"94949","message_id":"00059df022589746e863","timestamp":"1478692750","transmission_id":"102475407946750991","accept_language":"en-US,en;q=0.5","rcpt_to":"james@acw.uk.com","type":"open","template_id":"template_102475407946750991","geo_ip":{"country":"GB","region":"L9","city":"Sheffield","latitude":53.3429,"longitude":-1.3495},"raw_rcpt_to":"james@acw.uk.com"}}}]';
//    foreach(json_decode($responseV) as $one) {
//        var_dump($one->msys->track_event);
//
//        $agent->setUserAgent($one->msys->track_event->user_agent);
//        var_dump($agent->browser());
//        var_dump($agent->platform());
//        var_dump($agent->version($agent->platform()));
//    }
//
    $response5 = '[{"msys":{"track_event":{"sending_ip":"54.244.48.140","target_link_url":"http:\/\/www.youtube.com\/watch?v=npk7tfKyXok","timestamp":"1478686788","customer_id":"94949","campaign_id":"10","template_version":"0","user_agent":"Mozilla\/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko\/20100101 Firefox\/49.0","ip_address":"81.137.64.45","rcpt_tags":[],"type":"click","event_id":"84461242397892527","accept_language":"en-US,en;q=0.5","delv_method":"esmtp","rcpt_to":"james@acw.uk.com","ip_pool":"shared","rcpt_meta":[],"message_id":"00059df022589746e863","template_id":"template_102475407946750991","transmission_id":"102475407946750991","geo_ip":{"country":"GB","region":"L9","city":"Sheffield","latitude":53.3429,"longitude":-1.3495},"raw_rcpt_to":"james@acw.uk.com"}}},{"msys":{"track_event":{"template_version":"0","delv_method":"esmtp","rcpt_tags":[],"customer_id":"94949","accept_language":"en-US,en;q=0.5","rcpt_to":"james@acw.uk.com","rcpt_meta":[],"user_agent":"Mozilla\/5.0 (Windows NT 10.0; WOW64; rv:49.0) Gecko\/20100101 Firefox\/49.0","timestamp":"1478686794","transmission_id":"102475407946750991","target_link_url":"http:\/\/care.email-newsletter.info\/email\/inside-ci-news-from-the-ci-secretariat-october-2016#first-supervisory-board-meeting","type":"click","sending_ip":"54.244.48.140","ip_pool":"shared","template_id":"template_102475407946750991","ip_address":"81.137.64.45","campaign_id":"10","event_id":"30417853766434932","message_id":"00059df022589746e863","geo_ip":{"country":"GB","region":"L9","city":"Sheffield","latitude":53.3429,"longitude":-1.3495},"raw_rcpt_to":"james@acw.uk.com"}}},{"msys":{"track_event":{"type":"open","template_version":"0","message_id":"00059df022589746e863","user_agent":"Mozilla\/4.0 (compatible; MSIE 7.0; Windows NT 10.0; WOW64; Trident\/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; Microsoft Outlook 16.0.7369; ms-office; MSOffice 16)","event_id":"84461242397896529","sending_ip":"54.244.48.140","transmission_id":"102475407946750991","ip_pool":"shared","rcpt_to":"james@acw.uk.com","delv_method":"esmtp","customer_id":"94949","rcpt_tags":[],"timestamp":"1478686809","rcpt_meta":[],"ip_address":"81.137.64.45","campaign_id":"10","template_id":"template_102475407946750991","geo_ip":{"country":"GB","region":"L9","city":"Sheffield","latitude":53.3429,"longitude":-1.3495},"raw_rcpt_to":"james@acw.uk.com"}}}]';

    foreach(json_decode($response5) as $one) {
        var_dump($one->msys->track_event);
        $agent->setUserAgent($one->msys->track_event->user_agent);
        var_dump($agent->browser());
        var_dump($agent->platform());
        var_dump($agent->version($agent->platform()));
    }

//    $response6 = '[{"msys":{"track_event":{"rcpt_tags":[],"rcpt_meta":[],"template_id":"template_102475407946750991","transmission_id":"102475407946750991","event_id":"66446960124337336","user_agent":"Mozilla\/4.0 (compatible; MSIE 7.0; Windows NT 10.0; WOW64; Trident\/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; Microsoft Outlook 16.0.7369; ms-office; MSOffice 16)","timestamp":"1478687084","ip_pool":"shared","ip_address":"81.137.64.45","delv_method":"esmtp","message_id":"00059df022589746e863","campaign_id":"10","sending_ip":"54.244.48.140","template_version":"0","customer_id":"94949","rcpt_to":"james@acw.uk.com","type":"open","geo_ip":{"country":"GB","region":"L9","city":"Sheffield","latitude":53.3429,"longitude":-1.3495},"raw_rcpt_to":"james@acw.uk.com"}}}]';
//
//    foreach(json_decode($response6) as $one) {
//        var_dump($one->msys->track_event);
//    }

});

Route::get('unsubscribe', ['as' => 'subscribers.unsubscribe', function() { return 'Unsubscribed'; }]);

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('email-templates/{id}/display', ['as' => 'email-templates.display', 'uses' => 'EmailTemplateController@externalDisplay']);
Route::get('emails/{id}/display', ['as' => 'emails.display', 'uses' => 'EmailController@externalDisplay']);

Route::group(['middleware' => 'auth'], function () {
    if ( ! request()->ajax() ) {
        Route::get('subscribers/export', 'SubscriberController@export');
        Route::get('subscribers/{vue?}', 'SubscriberController@index')->where('vue', '[\/\w\.-]*');
        Route::get('email-templates/export', 'EmailTemplateController@export');
        Route::get('email-templates/{vue?}', 'EmailTemplateController@index')->where('vue', '[\/\w\.-]*');
        Route::get('email-editions/{vue?}', 'EmailEditionController@index')->where('vue', '[\/\w\.-]*');
        Route::get('emails/export', 'EmailController@export');
        Route::get('emails/{vue?}', 'EmailController@index')->where('vue', '[\/\w\.-]*');
    }
});

Route::get('home',['as' => 'home', function(){ return redirect(route('dashboard')); }]);
Route::get('dashboard', ['as' => 'dashboard', 'uses' => 'AvaController@index']);
Route::put('subscribers/{option}/quick-edit', 'SubscriberController@quickUpdate');
Route::post('subscribers/finalise-import', 'SubscriberController@finaliseImport');
Route::resource('subscribers', 'SubscriberController');
Route::resource('mailing-lists', 'MailingListController');
Route::put('email-templates/{option}/quick-edit', 'EmailTemplateController@quickUpdate');
Route::resource('email-templates', 'EmailTemplateController');
Route::resource('email-editions', 'EmailEditionController');
Route::put('emails/{option}/quick-edit', 'EmailController@quickUpdate');
Route::resource('emails', 'EmailController');


Route::get('search', function(\Illuminate\Http\Request $request) {
    $results = \App\Subscriber::getSearchResults($request->search);

    foreach( $results as $result) {
        var_dump($result->first_name);
        var_dump($result->last_name);
        var_dump($result->email);
        echo "<br />";
    }
    var_dump($results);
});
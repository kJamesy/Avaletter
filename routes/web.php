<?php
Route::get('lab', function() {
    $when = \Carbon\Carbon::now()->addMinute(1);
    $email = \App\EmailTemplate::getTemplate(17);

    $subscribers = \App\Subscriber::whereIn('id', [1,2,3,4])->get();
//    $subscriber = \App\Subscriber::first();

    if ( $subscribers ) {
        $recipients = [];
        foreach($subscribers as $subscriber) {
            $recipients[] = [
                'address' => [
                    'name' => "$subscriber->first_name $subscriber->last_name",
                    'email' => $subscriber->email
                ],
                'substitution_data' => [
                    'id' => $subscriber->id,
                    'name' => $subscriber->first_name,
                    'unsubscribe_link' => "<a href='http://www.example.com/unsub_handler?email=$subscriber->email' data-msys-unsubscribe='1'>Unsubscribe</a>"
                ],
            ];
        }

        $httpClient = new \Http\Adapter\Guzzle6\Client(new \GuzzleHttp\Client());
        $sparky = new \SparkPost\SparkPost($httpClient, ['key' => env('SPARKPOST_SECRET')]);

        $promise = $sparky->transmissions->post([
            'content' => [
                'from' => [
                    'name' => "Ava Lovelace",
                    'email' => "hello@ava.email-newsletter.info",
                ],
                'subject' => 'SparkPost Test',
                'html' => '<html><body><h1>It works, {{name}}!</h1><p>You just sent your very first mailing!</p> <p>{{ unsubscribe_link }}</p></body></html>',
                'text' => 'Congratulations, {{name}}!! You just sent your very first mailing!',
            ],
            'recipients' => $recipients,
        ]);

//        $promise = $sparky->transmissions->get();

        $sparky->setOptions(['async' => false]);
        try {
            $response = $sparky->transmissions->get();

            var_dump($response->getStatusCode()."\n");
            var_dump($response->getBody());
        }
        catch (\Exception $e) {
            echo $e->getCode()."\n";
            echo $e->getMessage()."\n";
        }

//        foreach ($subscribers as $subscriber) {
//            config(['services.sparkpost.options' =>
//                [
//                    'open_tracking' => false,
//                    'click_tracking' => false,
//                    'transactional' => true,
//                ]
//            ]);
//            $result = \Illuminate\Support\Facades\Mail::to($subscriber)->send(new \App\Mail\Newsletter($email, $subscriber));
//
//        }
    }

});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('email-templates/{id}/display', ['as' => 'email-templates.display', 'uses' => 'EmailTemplateController@externalDisplay']);
Route::group(['middleware' => 'auth'], function () {
    if ( ! request()->ajax() ) {
        Route::get('subscribers/export', 'SubscriberController@export');
        Route::get('subscribers/{vue?}', 'SubscriberController@index')->where('vue', '[\/\w\.-]*');
        Route::get('email-templates/export', 'EmailTemplateController@export');
        Route::get('email-templates/{vue?}', 'EmailTemplateController@index')->where('vue', '[\/\w\.-]*');
        Route::get('email-editions/{vue?}', 'EmailEditionController@index')->where('vue', '[\/\w\.-]*');
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
<?php
Route::any('webhooks', function () {
    return response()->json(['message' => 'It\'s working!'], 200);
});

Route::get('lab', function() {
    $http_client = new \Http\Adapter\Guzzle6\Client(new \GuzzleHttp\Client());
    $spark = new \SparkPost\SparkPost($http_client, [
        'key' => env('SPARKPOST_API_KEY'),
        'protocol' => 'http',
        'version' => 'v1',
//        'async' => false
    ]);

    $promise = $spark->transmissions->post([
        'content' => [
            'from' => [
                'name' => 'James Test',
                'email' => 'james@ava.email-newsletter.info',
            ],
            'subject' => 'First Mailing From PHP',
            'html' => '<html><body><h1>Congratulations, {{ name }}!</h1><p>You just sent your very first mailing!</p></body></html>',
            'text' => 'Congratulations, {{name}}!! You just sent your very first mailing!',
        ],
        'substitution_data' => ['name' => 'YOUR_FIRST_NAME'],
        'recipients' => [
            [
                'address' => [
                    'name' => 'Some Subscriber',
                    'email' => 'james@acw.uk.com',
                ],
            ],
        ],
    ]);

    var_dump($promise);
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
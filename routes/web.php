<?php
Route::get('lab', function() {
    $stats = \App\Email::getEmailCountriesStats(11, 20);

    var_dump($stats->toArray());

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

        Route::get('scout-reindex', function() {
            \App\Email::get()->searchable();
            \App\EmailClick::get()->searchable();
            \App\EmailDelivery::get()->searchable();
            \App\EmailEdition::get()->searchable();
            \App\EmailInjection::get()->searchable();
            \App\EmailOpen::get()->searchable();
            \App\EmailTemplate::get()->searchable();
            \App\MailingList::get()->searchable();
            \App\Subscriber::get()->searchable();

            dd('All indexed!');
        });
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
Route::post('emails/{id}/sent-email', 'EmailStatsController@getSentEmail');
Route::post('emails/{id}/sent-email/recipients', 'EmailStatsController@getSentEmailRecipients');
Route::post('emails/{id}/sent-email/general-stats', 'EmailStatsController@getSentEmailGeneralStats');
Route::post('emails/{id}/sent-email/{type}/stats', 'EmailStatsController@getSentEmailStats');
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
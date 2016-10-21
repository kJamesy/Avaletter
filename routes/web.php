<?php

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    if ( ! request()->ajax() ) {
        Route::get('subscribers/export', 'SubscriberController@export');
        Route::get('subscribers/{vue?}', 'SubscriberController@index')->where('vue', '[\/\w\.-]*');
    }
});

Route::get('home', function(){ return redirect(route('dashboard')); });
Route::get('dashboard', ['as' => 'dashboard', 'uses' => 'AvaController@index']);
Route::resource('subscribers', 'SubscriberController');
Route::put('subscribers/{option}/quick-edit', 'SubscriberController@quickUpdate');
Route::post('subscribers/finalise-import', 'SubscriberController@finaliseImport');
Route::resource('mailing-lists', 'MailingListController');

Route::get('test', function() {
    $filename = time() . '-All-Subscribers';
    $subscribers = \App\Subscriber::getAllSubscribers('created_at', 'asc', 0, 1);

    $exporter = new \App\AvaHelper\ExcelExporter($subscribers, $filename);

    return $exporter->generateSubscribersExport();
});
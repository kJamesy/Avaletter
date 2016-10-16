<?php

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('home', function(){ return redirect(route('dashboard')); });
Route::get('dashboard', ['as' => 'dashboard', 'uses' => 'AvaController@index']);
Route::resource('subscribers', 'SubscriberController');
Route::put('subscribers/{option}/quick-edit', 'SubscriberController@quickUpdate');
Route::resource('mailing-lists', 'MailingListController');

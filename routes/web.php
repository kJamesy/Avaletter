<?php

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('home', ['as' => 'dashboard', 'uses' => 'AvaController@index']);
Route::resource('subscribers', 'SubscriberController');
Route::resource('mailing-lists', 'MailingListController');

<?php

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('home', ['as' => 'dashboard', 'uses' => 'AvaController@index']);
Route::get('subscribers', ['as' => 'subscribers.landing', 'uses' => 'SubscriberController@index']);
Route::resource('mailing-lists', 'MailingListController');

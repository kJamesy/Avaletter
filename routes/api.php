<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::any('webhooks', function () {
    return response()->json(['message' => 'It\'s working!'], 200);
});

Route::post('postman', function() {
    return response()->json(['message' => 'Yaay! Works!'], 200);
});
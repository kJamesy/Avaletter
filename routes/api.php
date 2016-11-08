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

Route::post('sparkpost/webhooks', function (Request $request) {
    $feedback = json_encode($request->all());

    $sparky = new \App\SparkyResponse();
    $sparky->body = $feedback;
    $sparky->save();

    return response()->json(['message' => 'It\'s working!'], 200);
});

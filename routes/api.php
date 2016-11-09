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

    $sparky = new \App\SparkyResponse();
    $sparky->body = json_encode($request->all());
    $sparky->save();

    dispatch(new \App\Jobs\HandleSparkPostResponse($sparky));

    return response()->json(['message' => 'We\'ll take it from here, thank you.'], 200);
});

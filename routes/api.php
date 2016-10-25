<?php

use Illuminate\Support\Facades\Input;
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

Route::post('/sa-login', 'SuperAdminController@login');

//Route::post('sa-login', function() {
////    echo $request -> request -> all();
//    echo 'horee';
//});

//Route::get('sa-login', function() {
//    echo '<form action="sa" method="POST">';
////    echo '<input type="submit">';
//    echo '<input type="hidden" value="' . csrf_token() . '" name="_token">';
//    echo '</form>';
//});

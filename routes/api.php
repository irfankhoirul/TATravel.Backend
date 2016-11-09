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

Route::get('/db-connection', function () {
    $superAdmin = TATravel\User::find(1);
    echo $superAdmin->email;
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

//Route::get('/sa-login', 'UserController@login');

Route::post('/sa-login', 'UserController@login');

Route::post('/register', 'UserController@register');

Route::post();

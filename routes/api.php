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


/* Sprint 1 */

// Register
Route::post('/register', 'UserController@register');

// Verify phone number
Route::post('/verify', 'UserController@verify');

// Login by Phone
Route::post('/login', 'UserController@login');

// Update user (profile)
Route::post('/user/update/{id}', 'UserController@update')
    ->middleware('auth.basic');

// Get profile
Route::post('/user/{id}', 'UserController@show')
    ->middleware('auth.basic');

// Login driver
Route::post('/login-driver', 'UserController@loginDriver');

// Logout
Route::post('/logout', 'UserController@logout')
    ->middleware('auth.basic');

// Update fcm token
Route::post('/update-fcm-token', 'UserDeviceController@updateFCMToken')
    ->middleware('auth.basic');


/* Sprint 2 */

// Add penumpang 
Route::post('/user/{userId}/penumpang/create', 'PenumpangController@create')
    ->middleware('auth.basic');

// Update penumpang
Route::post('/user/{userId}/penumpang/update/{id}', 'PenumpangController@update')
    ->middleware('auth.basic');

// Delete penumpang
Route::post('/user/{userId}/penumpang/delete/{id}', 'PenumpangController@delete')
    ->middleware('auth.basic');

// Get List penumpang
Route::post('/user/{userId}/penumpang/list', 'PenumpangController@getList')
    ->middleware('auth.basic', 'auth.user');

// Get List City
Route::post('/city/list', 'KotaController@getList')
    ->middleware('auth.basic', 'auth.user');

// Get list operator travel
Route::post('/operator-travel/list', 'OperatorTravelController@getList')
    ->middleware('auth.basic', 'auth.user');

// Get detail operator travel
Route::post('/operator-travel/{id}', 'OperatorTravelController@show')
    ->middleware('auth.basic', 'auth.user');

// Get location list (Terminal departure & destination)
Route::post('/operator-travel/{id}/location', 'LokasiController@getList')
    ->middleware('auth.basic', 'auth.user');

// Get list available schedule (on that day)
Route::post('/operator-travel/{id}/schedule/list', 'JadwalPerjalananController@getList')
    ->middleware('auth.basic', 'auth.user');

// Get detail schedule
Route::post('/schedule/{id}', 'JadwalPerjalananController@show')
    ->middleware('auth.basic', 'auth.user');

// Get list driver schedule
Route::post('/driver/schedule/list', 'JadwalPerjalananController@driverScheduleList')
    ->middleware('auth.basic', 'auth.driver');

// Get detail driver schedule
Route::post('/driver/schedule/{id}', 'JadwalPerjalananController@showDriverScheduleDetail')
    ->middleware('auth.basic', 'auth.driver');

// Set status jadwal perjalanan (OTW, Arrived)
Route::post('/driver/schedule/status/set/{id}', 'JadwalPerjalananController@setStatus')
    ->middleware('auth.basic', 'auth.driver');

// List available seat
Route::post('/schedule/{id}/seat/list', 'KursiPerjalananController@getList')
    ->middleware('auth.basic', 'auth.user');

// Set seat booked
Route::post('/seat/book/{id}', 'KursiPerjalananController@bookSeat')
    ->middleware('auth.basic', 'auth.user');

// Order
Route::post('/reservation', 'PemesananController@reservation')
    ->middleware('auth.basic', 'auth.user');

// Get order detail
Route::post('/reservation/{id}', 'PemesananController@show');

// Get list order 
Route::post('/order/list', 'PemesananController@getList')
    ->middleware('auth.basic', 'auth.user');

// Set operator rating
Route::post('/rate/add', 'RatingPerjalananController@rate')
    ->middleware('auth.basic', 'auth.user');

// Update operator rating
Route::post('rate/update/{id}', 'RatingPerjalananController@update')
    ->middleware('auth.basic', 'auth.user');

// Delete operator rating
Route::post('rate/delete/{id}', 'RatingPerjalananController@delete')
    ->middleware('auth.basic', 'auth.user');

/*
 * To Do
 */

// Payment


/* OLD */
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

/* Get Available Schedule */
Route::post('/search', 'JadwalPerjalananController@availableSchedule');

/* Get Location */
Route::post('/location', 'LokasiController@availableLocation');


/* Get Operator Travel */
Route::post('/operator', 'OperatorTravelController@getOperatorTravel');

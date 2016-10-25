<?php

namespace TATravel\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use TATravel\Http\Requests;

class SuperAdminController extends Controller {

    //

    public function login(Request $request) {
        print_r($request -> request -> all());
//        $token = Input::post('token');
//        echo "Your Token: " . $token;
    }

}

<?php

namespace TATravel\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request) {
        $user = DB::table('user')->where('email', $request->request->get('email'))->first();
        $toBeHashed = $request->request->get('password').$user->salt;

        // Check username + password        
        if(strtoupper(hash('sha512', $toBeHashed)) == $user->password){
            echo "OKKE";
        } else {
            echo "Not Okke";
        }
        
        // Generate token
        
    }
}

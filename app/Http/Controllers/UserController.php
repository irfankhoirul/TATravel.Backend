<?php

namespace TATravel\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserController
 * @package TATravel\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * @param Request $request
     */
    public function _login(Request $request)
    {
        $user = DB::table('user')->where('email', $request->request->get('email'))->first();
        $toBeHashed = $request->request->get('password') . $user->salt;


        // Check username + password        
        if (strtoupper(hash('sha512', $toBeHashed)) == $user->password) {
            echo "OKKE";
        } else {
            echo "Not Okke";
        }

        // Generate token

    }

    /* - - - - - - - - - - - */


    /**
     * Step :
     * - Validasi data, pastikan email / no hp belum digunakan untuk registrasi
     * - Encript password + generated salt
     * - Insert user ke tabel user, set status Draft
     * - Send SMS and Email confirmation code
     * @param   Request $request Post data dari request
     * @return  TRUE jika registrasi berhasil, FALSE jika gagal
     */
    public function register(Request $request)
    {

    }

    public function registerWithFacebook()
    {

    }

    public function registerWithGoogle()
    {

    }

    public function login(Request $request)
    {

    }

    public function loginWithFacebook()
    {

    }

    public function loginWithGoogle()
    {

    }

    public function verify()
    {

    }

    public function resendConfirmationCode()
    {

    }

    public function forgetPassword()
    {

    }

    public function resetPassword()
    {

    }

    public function updateProfile()
    {

    }

}

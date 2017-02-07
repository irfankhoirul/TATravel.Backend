<?php

namespace TATravel\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\User;
use Illuminate\Support\Facades\Hash;
use Validator;

/**
 * Class UserController
 * @package TATravel\Http\Controllers
 */
class UserController extends BaseController {

    /**
     * Step :
     * - Validasi data, pastikan email / no hp belum digunakan untuk registrasi
     * - Encript password + generated salt
     * - Insert user ke tabel user, set status Registered
     * - Send SMS and Email confirmation code
     * 
     * Post Data :
     * - Name       : Required
     * - Phone      : Required
     * - Email      : Optional
     * - Password   : Required
     * @param   Request $request Post data dari request
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|max:128',
                    'phone' => 'required|digits_between:3,128',
                    'email' => 'email|max:128',
                    'password' => 'required',
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $userData['name'] = $request->request->get('name');
        $userData['phone'] = $request->request->get('phone');
        $userData['email'] = $request->request->get('email');
        $userData['password'] = $request->request->get('password');

        $user = new User();
        list($status, $message, $technicalMessage) = $user->register($userData);
        $this->returnJson($status, $message, $technicalMessage, null);
    }
    
    /**
     * Post Data :
     * - VerificationCode   : Required
     * - Phone              : Required
     * @param   Request $request Post data dari request
     */
    public function verify(Request $request){
        $validator = Validator::make($request->all(), [
                    'registrationCode' => 'required|max:5',
                    'phone' => 'required|digits_between:3,128',
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }
        
        $verificationData['phone'] = $request->request->get('phone');
        $verificationData['registrationCode'] = $request->request->get('registrationCode');
        
        $user = new User();
        list($status, $message, $technicalMessage) = $user->verify($verificationData);
        $this->returnJson($status, $message, $technicalMessage, null);
        
    }

    /**
     * Post Data :
     * - Phone              : Required
     * - Password           : Required
     * - deviceSecretCode   : Required
     * @param Request $request
     */
    public function login(Request $request) {
        $user = DB::table('user')->where('email', $request->request->get('email'))->first();
        $toBeHashed = $request->request->get('password') . $user['salt'];

        // Check username + password
        if (strtoupper(hash('sha512', $toBeHashed)) == $user['password']) {
            echo "OKKE";
        } else {
            echo "Not Okke";
        }

        // Generate token
    }

    /* - - - - - - - - - - - */

    public function registerWithFacebook() {
        
    }

    public function registerWithGoogle() {
        
    }

//    public function login(Request $request)
//    {
//
//    }

    public function loginWithFacebook() {
        
    }

    public function loginWithGoogle() {
        
    }

    public function resendConfirmationCode() {
        
    }

    public function forgetPassword() {
        
    }

    public function resetPassword() {
        
    }

    public function updateProfile() {
        
    }

}

<?php

namespace TATravel\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\User;
use TATravel\UserDevice;
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
     * - Name           : Required
     * - Phone          : Required
     * - Email          : Optional
     * - Password       : Required
     * - deviceSecretId : Required
     * @param   Request $request Post data dari request
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|max:128',
                    'phone' => 'required|digits_between:3,128',
                    'email' => 'email|max:128',
                    'password' => 'required',
                    'deviceSecretId' => 'required'
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
        if ($status == self::CODE_SUCCESS) {
            $userDevice = new UserDevice();
            list($statusDevice, $messageDevice, $technicalMessageDevice) = $userDevice->registerDevice($request->request->get('deviceSecretId'), $technicalMessage);
            $this->returnJson($status, $message, $technicalMessage, null);
        }
        $this->returnJson($status, $message, $technicalMessage, null);
    }

    /**
     * Post Data :
     * - VerificationCode   : Required
     * - Phone              : Required
     * @param   Request $request Post data dari request
     */
    public function verify(Request $request) {
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
        $validator = Validator::make($request->all(), [
                    'deviceSecretId' => 'required',
                    'phone' => 'required|digits_between:3,128',
                    'password' => 'required',
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $userData['phone'] = $request->request->get('phone');
        $userData['password'] = $request->request->get('password');
        $deviceSecretCode = $request->request->get('deviceSecretId');

        $user = new User();
        list($status, $message, $technicalMessage, $data) = $user->login($userData, $deviceSecretCode);
        $this->returnJson($status, $message, $technicalMessage, $data);
    }

    /**
     * Post Data :
     * - Name       : Required
     * - Email      : Optional
     * - Password   : Optional
     * - Alamat     : Optional
     * - CityId     : Optional
     * - ProvinceId : Optional
     */
    public function update($id, Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|max:128',
                    'email' => 'max:128',
                    'password' => 'max:128',
                    'alamat' => 'max:128',
                    'cityid' => 'digits',
                    'provinceId' => 'digits'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $userData['nama'] = $request->request->get('name');
        $userData['email'] = $request->request->get('email');
        $userData['password'] = $request->request->get('password');
        $userData['alamat'] = $request->request->get('alamat');
        $userData['id_kota'] = $request->request->get('cityid');
        $userData['id_provinsi'] = $request->request->get('provinceId');

        $user = new User();
        if ($user->isTokenOwner($id, $request->request->get('token'))) {
            list($status, $message, $technicalMessage, $data) = $user->updateUser($id, $userData);
            $this->returnJson($status, $message, $technicalMessage, $data);
        }
        $this->returnJsonErrorNoAccess();
    }

    public function show($id, Request $request) {
        $user = new User();
        if ($user->isTokenOwner($id, $request->request->get('token'))) {
            list($status, $message, $technicalMessage, $data) = $user->show($id);
            $this->returnJson($status, $message, $technicalMessage, $data);
        }
        $this->returnJsonErrorNoAccess();
    }
    
    public function loginDriver(Request $request){
        $validator = Validator::make($request->all(), [
                    'deviceSecretId' => 'required',
                    'phone' => 'required|digits_between:3,128',
                    'password' => 'required',
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $userData['phone'] = $request->request->get('phone');
        $userData['password'] = $request->request->get('password');
        $deviceSecretCode = $request->request->get('deviceSecretId');

        $user = new User();
        list($status, $message, $technicalMessage, $data) = $user->loginDriver($userData, $deviceSecretCode);
        $this->returnJson($status, $message, $technicalMessage, $data);
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

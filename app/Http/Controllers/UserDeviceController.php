<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\UserDevice;
use TATravel\UserToken;
use Validator;

class UserDeviceController extends BaseController {

    public function updateFCMToken(Request $request) {
        $validator = Validator::make($request->all(), [
                    'token' => 'required',
                    'FCMToken' => 'required'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $token = $request->request->get('token');
        $FCMToken = $request->request->get('FCMToken');

        $userToken = new UserToken();
        list($status, $message, $technicalMessage, $data) = $userToken->getToken($token);
        if ($status == self::CODE_SUCCESS) {
            $userDevice = new UserDevice();
            list($status, $message, $technicalMessage, $data) = $userDevice->show($data['id_user_device']);
            if($status == self::CODE_SUCCESS) {
                list($status, $message, $technicalMessage) = $userDevice->updateFCMToken($data['secret_code'], $FCMToken);
            }            
        }

        $this->returnJson($status, $message, $technicalMessage, null);
    }

}

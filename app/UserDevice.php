<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserDevice extends BaseModel {

    const RESULT_UPDATE_FCMTOKEN_FAILED = "Gagal mengupdate FCM Token";
    const RESULT_UPDATE_FCMTOKEN_SUCCESS = "Berhasil mengupdate FCM Token";

    protected $table = 'user_device';

    public function registerDevice($deviceSecretId, $idUser) {
        try {
            $id = DB::table('user_device')->insertGetId(
                    ['id_user' => $idUser,
                        'secret_code' => $deviceSecretId
                    ]
            );
            return array(self::CODE_SUCCESS, NULL, $id);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

    public function updateFCMToken($deviceSecretCode, $FCMToken) {
        try {
            $result = DB::table('user_device')
                    ->where('secret_code', $deviceSecretCode)
                    ->update(['FCM_token' => $FCMToken]);
            return array(self::CODE_SUCCESS, self::RESULT_UPDATE_FCMTOKEN_SUCCESS, NULL);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_UPDATE_FCMTOKEN_SUCCESS, $ex->getMessage());
        }
    }
    
    public function show($id){
        try {
            $userDevice = DB::table($this->table)->where('id', $id)->first();
            return array(self::CODE_SUCCESS, NULL, NULL, $userDevice);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

}

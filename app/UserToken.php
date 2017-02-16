<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class UserToken extends BaseModel {

    const RESULT_TOKEN_NOT_FOUND = 'Token tidak ditemukan';
    const RESULT_TOKEN_EXPIRED = 'Session expired';


    protected $table = 'user_token';

    public function createToken($userId, $deviceId) {
        try {
            $expiredTimeMilis = time() + (30 * 24 * 60 * 60); // Expired dalam 30 hari
            $expiredTime = date("Y-m-d H:i:s.u", $expiredTimeMilis);
            $id = DB::table($this->table)->insertGetId(
                    ['expired_at' => $expiredTime,
                        'id_user' => $userId,
                        'id_user_device' => $deviceId,
                        'status' => self::STATUS_ACTIVE,
                        'token' => str_random(256),
                        'total_request' => 1
                    ]
            );
            return array(self::CODE_SUCCESS, NULL, $id);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

    public function checkToken($token) {
        try {
            $userToken = DB::table($this->table)->where('token', $token)->first();

            // Check if exist
            if (empty($userToken)) {
                return array(self::CODE_ERROR, self::RESULT_TOKEN_NOT_FOUND, NULL);
            }

            // Check if expired
            $now = time(); // Expired dalam 30 hari
            $expiredTime = strtotime($userToken['expired_at']);
            if ($now > $expiredTime) {
                $result = DB::table($this->table)
                    ->where('token', $token)
                    ->update(['status' => self::STATUS_EXPIRED]);
                return array(self::CODE_ERROR, self::RESULT_TOKEN_EXPIRED, NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

    public function incrementRequestCount($token) {
        try {
            $userToken = DB::table($this->table)->where('token', $token)->first();
            
            $result = DB::table($this->table)
                    ->where('token', $token)
                    ->update(['total_request' => $userToken['total_request'] + 1]);
            return array(self::CODE_SUCCESS, NULL, NULL);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }
    
    public function getToken($token){
        try {
            $userToken = DB::table($this->table)->where('token', $token)->first();
            return array(self::CODE_SUCCESS, NULL, NULL, $userToken);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

}

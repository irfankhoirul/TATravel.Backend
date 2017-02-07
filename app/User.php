<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class User extends BaseModel {

    const USER_TYPE_USER = 'U';
    const USER_TYPE_ADMIN = 'A';
    const USER_TYPE_SUPER_ADMIN = 'S';
    const USER_TYPE_DRIVER = 'D';
    const USER_REGISTRATION_STEP_REGISTERED = 'D';
    const USER_REGISTRATION_STEP_VERIFIED = 'V';
    const RESULT_REGISTRATION_SUCCESS = "Registrasi berhasil";
    const RESULT_REGISTRATION_FAILED = "Registrasi gagal";
    const RESULT_VERIFICATION_SUCCESS = "Verifikasi berhasil";
    const RESULT_VERIFICATION_FAILED = "Verifikasi gagal";
    const RESULT_VERIFICATION_NO_NEEDED = "Anda sudah terverifikasi";
    const RESULT_USER_NOT_FOUND = "User tidak ditemukan";
    const RESULT_WRONG_REGISTRATION_CODE = "Kode registrasi salah";

    protected $table = 'user';

    public function register($userData) {
        $salt = str_random(64);
        $registrationCode = rand(10000, 99999);
        try {
            $id = DB::table('user')->insertGetId(
                    ['nama' => $userData['name'],
                        'nomor_handphone' => $userData['phone'],
                        'email' => $userData['email'],
                        'password' => hash('sha512', $salt . hash('md5', $userData['password'] . $salt)),
                        'salt' => $salt,
                        'registration_step' => self::USER_REGISTRATION_STEP_REGISTERED,
                        'tipe' => self::USER_TYPE_USER,
                        'registration_code' => $registrationCode
                    ]
            );
            $this->sendSmsVerification($registrationCode, $userData['phone']);
            return array(self::CODE_SUCCESS, self::RESULT_REGISTRATION_SUCCESS, $id);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_REGISTRATION_FAILED, $ex->getMessage());
        }
    }

    public function sendSmsVerification($registrationCode, $phone) {
        // Script http API SMS Reguler Zenziva
        $userkey = 'mu9z1h'; // userkey lihat di zenziva
        $passkey = '15c202f3734b5cf7dc18cfcaace6c5bc'; // set passkey di zenziva
        $message = 'Kode Registrasi TATravel Anda : ' . $registrationCode;

        $url = 'https://reguler.zenziva.net/apps/smsapi.php';
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, 'userkey=' . $userkey . '&passkey=' . $passkey . '&nohp=' . $phone . '&pesan=' . urlencode($message));
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_POST, 1);

        $results = curl_exec($curlHandle);

        curl_close($curlHandle);
    }

    public function verify($verificationData) {
        try {
            $user = DB::table('user')->where('nomor_handphone', $verificationData['phone'])->first();
            if (!empty($user)) {
                if ($user['registration_step'] == self::USER_REGISTRATION_STEP_VERIFIED) {
                    return array(self::CODE_ERROR, self::RESULT_VERIFICATION_NO_NEEDED, NULL);
                } else if ($verificationData['registrationCode'] == $user['registration_code']) {
                    try {
                        $result = DB::table('user')
                                ->where('nomor_handphone', $verificationData['phone'])
                                ->update(['registration_step' => self::USER_REGISTRATION_STEP_VERIFIED]);
                        if ($result == self::QUERY_SUCCESS) {
                            return array(self::CODE_SUCCESS, self::RESULT_VERIFICATION_SUCCESS, NULL);
                        } else {
                            return array(self::CODE_ERROR, self::RESULT_VERIFICATION_FAILED, NULL);
                        }
                    } catch (\Illuminate\Database\QueryException $ex) {
                        return array(self::CODE_ERROR, self::RESULT_VERIFICATION_FAILED, $ex->getMessage());
                    }
                } else {
                    return array(self::CODE_ERROR, self::RESULT_WRONG_REGISTRATION_CODE, NULL);
                }
            } else {
                return array(self::CODE_ERROR, self::RESULT_USER_NOT_FOUND, NULL);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_VERIFICATION_FAILED, $ex->getMessage());
        }
    }

}

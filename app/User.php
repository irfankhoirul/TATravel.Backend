<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\UserDevice;
use TATravel\UserToken;

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
    const RESULT_LOGIN_FAILED = "Login gagal";
    const RESULT_LOGIN_SUCCESS = "Login berhasil";
    const RESULT_UPDATE_SUCEESS = "Berhasil mengupdate profil";
    const RESULT_UPDATE_FAILED = "Gagal mengupdate profil";
    const RESULT_GET_PROFILE_SUCCESS = "Berhasil mendapatkan data profil";
    const RESULT_GET_PROFILE_FAILED = "Gagal mendapatkan data profil";

    protected $table = 'user';
    
    public function getUser($id){
        return DB::table('user')->where('id', $id)->first();
    }

    public function isTokenOwner($id, $token) {
        $userToken = new UserToken();
        list($status, $message, $technicalMessage, $data) = $userToken->getToken($token);
        if ($data['id_user'] != $id) {
            return FALSE;
        }
        return TRUE;
    }

    public function register($userData) {
        $salt = str_random(64);
        $registrationCode = rand(10000, 99999);
        try {
            $id = DB::table($this->table)->insertGetId(
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
        } catch (QueryException $ex) {
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
                    $result = DB::table($this->table)
                            ->where('nomor_handphone', $verificationData['phone'])
                            ->update(['registration_step' => self::USER_REGISTRATION_STEP_VERIFIED]);
                    if ($result == self::QUERY_SUCCESS) {
                        return array(self::CODE_SUCCESS, self::RESULT_VERIFICATION_SUCCESS, NULL);
                    } else {
                        return array(self::CODE_ERROR, self::RESULT_VERIFICATION_FAILED, NULL);
                    }
                } else {
                    return array(self::CODE_ERROR, self::RESULT_WRONG_REGISTRATION_CODE, NULL);
                }
            } else {
                return array(self::CODE_ERROR, self::RESULT_USER_NOT_FOUND, NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_VERIFICATION_FAILED, $ex->getMessage());
        }
    }

    public function login($userData, $deviceSecretCode) {
        // Validasi login
        try {
            $user = DB::table($this->table)->where('nomor_handphone', $userData['phone'])->first();
            if ($user['password'] == hash('sha512', $user['salt'] . hash('md5', $userData['password'] . $user['salt']))) {
                // Get device by secret id, jika tidak ada, add
                $device = DB::table('user_device')->where('secret_code', $deviceSecretCode)->first();
                if (empty($device)) {
                    $userDevice = new UserDevice();
                    list($statusDevice, $messageDevice, $technicalMessageDevice) = $userDevice->registerDevice($deviceSecretCode, $user['id']);
                    if ($statusDevice != self::CODE_SUCCESS) {
                        return array(self::CODE_ERROR, $messageDevice, $technicalMessageDevice, NULL);
                    }
                    $userDeviceId = $technicalMessageDevice;
                } else {
                    $userDeviceId = $device['id'];
                }

                // Create token
                $userToken = new UserToken();
                list($statusToken, $messageToken, $technicalMessageToken) = $userToken->createToken($user['id'], $userDeviceId);
                $userToken = DB::table('user_token')->where('id', $technicalMessageToken)->first();

                // Return User data (session data)
                if ($statusToken == self::CODE_SUCCESS) {
                    $user['token'] = $userToken;
                    return array(self::CODE_ERROR, self::RESULT_LOGIN_SUCCESS, NULL, $user);
                }

                return array(self::CODE_ERROR, self::RESULT_LOGIN_FAILED, $ex->getMessage(), NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_LOGIN_FAILED, $ex->getMessage(), NULL);
        }
    }

    public function updateUser($id, $userData) {
        $updateData = Array();
        $updateData['nama'] = $userData['nama'];
        if (!empty($userData['email'])) {
            $updateData['email'] = $userData['email'];
        }
        if (!empty($userData['password'])) {
            $user = DB::table($this->table)->where('id', $id)->first();
            $updateData['password'] = hash('sha512', $user['salt'] . hash('md5', $userData['password'] . $user['salt']));
        }
        if (!empty($userData['alamat'])) {
            $updateData['alamat'] = $userData['alamat'];
        }
        if (!empty($userData['id_kota'])) {
            $updateData['id_kota'] = $userData['id_kota'];
        }
        if (!empty($userData['id_provinsi'])) {
            $updateData['id_provinsi'] = $userData['id_provinsi'];
        }

        try {
            $result = DB::table($this->table)
                    ->where('id', $id)
                    ->update($updateData);
            list($status, $message, $technicalMessage, $data) = $this->show($id);
            return array(self::CODE_SUCCESS, self::RESULT_UPDATE_SUCEESS, NULL, $data);
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_UPDATE_FAILED, $ex->getMessage(), NULL);
        }
    }

    public function show($id) {
        try {
            $user = DB::table($this->table)->where('id', $id)->first();
            if (!empty($user['id_kota'])) {
                $user['kota'] = DB::table('kota')->where('id', $user['id_kota'])->first();
            }
            if (!empty($user['id_provinsi'])) {
                $user['provinsi'] = DB::table('provinsi')->where('id', $user['id_provinsi'])->first();
            }


            if ($user['tipe'] == self::USER_TYPE_USER) {
                return array(self::CODE_SUCCESS, self::RESULT_GET_PROFILE_SUCCESS, NULL, $user);
            } else if ($user['tipe'] == self::USER_TYPE_DRIVER) {
                $driver = DB::table('supir')->where('id_user', $user['id'])->first();
                $admin = DB::table('admin')->where('id', $driver['id_admin'])->first();
                $operatorTravel = DB::table('operator_travel')->where('id', $driver['id_operator_travel'])->first();
                $driver['user'] = $user;
                $driver['admin'] = $admin;
                $driver['operator_travel'] = $operatorTravel;
                return array(self::CODE_SUCCESS, self::RESULT_GET_PROFILE_SUCCESS, NULL, $driver);
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_GET_PROFILE_FAILED, $ex->getMessage(), NULL);
        }
    }

    public function loginDriver($userData, $deviceSecretCode) {
        // Validasi login
        try {
            $user = DB::table($this->table)->where('nomor_handphone', $userData['phone'])->first();
            if ($user['tipe'] !== self::USER_TYPE_DRIVER) {
                return array(self::CODE_ERROR, self::RESULT_USER_NOT_FOUND, NULL, NULL);
            }
            if ($user['password'] == hash('sha512', $user['salt'] . hash('md5', $userData['password'] . $user['salt']))) {
                // Get device by secret id, jika tidak ada, add
                $device = DB::table('user_device')->where('secret_code', $deviceSecretCode)->first();
                if (empty($device)) {
                    $userDevice = new UserDevice();
                    list($statusDevice, $messageDevice, $technicalMessageDevice) = $userDevice->registerDevice($deviceSecretCode, $user['id']);
                    if ($statusDevice != self::CODE_SUCCESS) {
                        return array(self::CODE_ERROR, $messageDevice, $technicalMessageDevice, NULL);
                    }
                    $userDeviceId = $technicalMessageDevice;
                } else {
                    $userDeviceId = $device['id'];
                }

                // Create token
                $userToken = new UserToken();
                list($statusToken, $messageToken, $technicalMessageToken) = $userToken->createToken($user['id'], $userDeviceId);
                $userToken = DB::table('user_token')->where('id', $technicalMessageToken)->first();

                // Return User data (session data)
                if ($statusToken == self::CODE_SUCCESS) {
                    $user['token'] = $userToken;
                    $driver = DB::table('supir')->where('id_user', $user['id'])->first();
                    $driver['user'] = $user;
                    return array(self::CODE_ERROR, self::RESULT_LOGIN_SUCCESS, NULL, $driver);
                }

                return array(self::CODE_ERROR, self::RESULT_LOGIN_FAILED, $ex->getMessage(), NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_LOGIN_FAILED, $ex->getMessage(), NULL);
        }
    }

}

<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class Pemesanan extends BaseModel {

    protected $table = 'pemesanan';

    public function reservation($idUser, $idJadwalPerjalanan) {
        $reservationCode = "RES-" . time() . rand(100, 999);
        try {
            $id = DB::table($this->table)->insertGetId(
                    ['id_user' => $idUser,
                        'id_jadwal_perjalanan' => $idJadwalPerjalanan,
                        'kode_pemesanan' => $reservationCode
                    ]
            );
            return array(self::CODE_SUCCESS, NULL, $id);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

    public function isBookerUser($userId, $scheduleId) {
        try {
            $reservation = DB::table($this->table)
                    ->where('id_user', $userId)
                    ->where('id_jadwal_perjalanan', $scheduleId)
                    ->first();

            if ($reservation != NULL) {
                return TRUE;
            } else {
                return FALSE;
            }
        } catch (QueryException $ex) {
            return FALSE;
        }
    }

    public function show($id) {
        try {
            $reservation = DB::table($this->table)
                    ->where('id', $id)
                    ->first();
            if ($reservation != NULL) {
                $reservation['jadwal_perjalanan'] = DB::table('jadwal_perjalanan')
                        ->where('id', $reservation['id_jadwal_perjalanan'])
                        ->first();
                $reservation['jadwal_perjalanan']['operator_travel'] = DB::table('operator_travel')
                        ->where('id', $reservation['jadwal_perjalanan']['id_operator_travel'])
                        ->first();
                $reservation['pembayaran'] = DB::table('pembayaran')
                        ->where('id_pemesanan', $id)
                        ->first();
                return array(self::CODE_SUCCESS, NULL, NULL, $reservation);
            } else {
                return array(self::CODE_ERROR, "Tidak ada data", NULL, NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

}

<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class Penumpang extends BaseModel {

    const RESULT_ADD_PASSENGER_SUCCESS = "Berhasil menambahkan penumpang";
    const RESULT_ADD_PASSENGER_FAILED = "Gagal menambahkan penumpang";
    const RESULT_UPDATE_PASSENGER_SUCCESS = "Berhasil mengubah penumpang";
    const RESULT_UPDATE_PASSENGER_FAILED = "Gagal mengubah penumpang";
    const RESULT_DELETE_PASSENGER_SUCCESS = "Berhasil menghapus penumpang";
    const RESULT_DELETE_PASSENGER_FAILED = "Gagal menghapus penumpang";

    protected $table = 'penumpang';

    public function getPenumpang($id) {
        try {
            $penumpang = DB::table($this->table)->where('id', $id)->first();
            return array(self::CODE_SUCCESS, NULL, NULL, $penumpang);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }
    
    public function listPenumpang($userId) {
        try {
            $penumpangs = DB::table($this->table)->where('id_user', $userId)->get();
            return array(self::CODE_SUCCESS, NULL, NULL, $penumpangs);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

    public function createPenumpang($passengerData) {
        try {
            $id = DB::table($this->table)->insertGetId(
                    ['id_user' => $passengerData['id_user'],
                        'nama' => $passengerData['nama']
                    ]
            );
            $penumpang = DB::table($this->table)->where('id', $id)->first();
            return array(self::CODE_SUCCESS, self::RESULT_ADD_PASSENGER_SUCCESS, NULL, $penumpang);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_ADD_PASSENGER_FAILED, $ex->getMessage(), NULL);
        }
    }

    public function updatePenumpang($passengerData) {
        try {
            DB::table($this->table)
                    ->where('id', $passengerData['id'])
                    ->update(['nama' => $passengerData['nama']]);
            $penumpang = DB::table($this->table)->where('id', $passengerData['id'])->first();
            return array(self::CODE_SUCCESS, self::RESULT_UPDATE_PASSENGER_SUCCESS, NULL, $penumpang);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_UPDATE_PASSENGER_FAILED, $ex->getMessage(), NULL);
        }
    }

    public function deletePenumpang($id) {
        try {
            DB::table($this->table)
                    ->where('id', $id)
                    ->delete();
            return array(self::CODE_SUCCESS, self::RESULT_DELETE_PASSENGER_SUCCESS, NULL);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_DELETE_PASSENGER_FAILED, $ex->getMessage());
        }
    }

}

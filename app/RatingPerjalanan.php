<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class RatingPerjalanan extends BaseModel {

    protected $table = 'rating_perjalanan';

    const RESULT_SET_RATING_SUCCESS = "Berhasil memberi rating";
    const RESULT_SET_RATING_FAILED = "Gagal memberi rating";
    const RESULT_UPDATE_RATING_SUCCESS = "Berhasil mengubah rating";
    const RESULT_UPDATE_RATING_FAILED = "Gagal mengubah rating";
    const RESULT_DELETE_RATING_SUCCESS = "Berhasil menghapus rating";
    const RESULT_DELETE_RATING_FAILED = "Gagal menghapus rating";
    
    public function show($id){
        try {            
            $rating = DB::table($this->table)->where('id', $id)->first();
            return array(self::CODE_SUCCESS, self::RESULT_UPDATE_RATING_SUCCESS, NULL, $rating);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_UPDATE_RATING_FAILED, $ex->getMessage(), NULL);
        }
    }

    public function hasRating($idUser, $idJadwalPerjalanann) {
        try {
            $reservation = DB::table($this->table)
                    ->where('id_user', $idUser)
                    ->where('id_jadwal_perjalanan', $idJadwalPerjalanann)
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

    public function rate($userId, $idJadwalPerjalanann, $rating, $review) {
        try {
            $id = DB::table($this->table)->insertGetId(
                    ['id_jadwal_perjalanan' => $idJadwalPerjalanann,
                        'id_user' => $userId,
                        'rating' => $rating,
                        'review' => $review
                    ]
            );
            return array(self::CODE_SUCCESS, self::RESULT_SET_RATING_SUCCESS, $id);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_SET_RATING_FAILED, $ex->getMessage());
        }
    }

    public function updateRating($ratingId, $rating, $review) {
        try {
            DB::table($this->table)
                    ->where('id', $ratingId)
                    ->update(
                            ['rating' => $rating,
                                'review' => $review]);
            $updatedRating = DB::table($this->table)->where('id', $ratingId)->first();
            return array(self::CODE_SUCCESS, self::RESULT_UPDATE_RATING_SUCCESS, NULL, $updatedRating);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_UPDATE_RATING_FAILED, $ex->getMessage(), NULL);
        }
    }

    public function deleteRating($id) {
        try {
            DB::table($this->table)
                    ->where('id', $id)
                    ->delete();
            return array(self::CODE_SUCCESS, self::RESULT_DELETE_RATING_SUCCESS, NULL);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_DELETE_RATING_FAILED, $ex->getMessage());
        }
    }

}

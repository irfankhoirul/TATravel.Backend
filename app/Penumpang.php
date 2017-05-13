<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\Util\DataPage;

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
    
    public function listPenumpang($userId, $page) {        
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                    ->where('id_user', $userId)
//                    ->groupBy('id')
//                    ->orderBy('id', 'asc')
                    ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                    ->where('id_user', $userId)
//                    ->groupBy('id')
//                    ->orderBy('id', 'asc')
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->get()
                    ->toArray();

            // Menghitung total page dari semua data yg bisa diperoleh
            $totalPage = $limit * $page != $dataCount ? $dataCount / $limit + 1 : $dataCount / $limit;
            
            // Menentukan apakah ada page selanjutnya
            $hasNext = count($datas) == $limit && $limit * $page != $dataCount;
            if ($hasNext) {
                $nextPage = $page + 1;
            } else {
                $nextPage = -1;
            }

            // Mengeset dataPage
            $dataPage = new DataPage();
            $dataPage = $dataPage->setTotalData($dataCount)
                    ->setTotalPage(intval($totalPage))
                    ->setCurrentPage($page)
                    ->setNextPage($nextPage)
                    ->get();
            
            // Return data
            return array(self::CODE_SUCCESS, NULL, NULL, $datas, $dataPage);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL, NULL);
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

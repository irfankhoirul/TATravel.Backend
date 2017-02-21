<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use TATravel\Util\DataPage;

class Kota extends BaseModel {

    const RESULT_GET_CITY_LIST_FAILED = "Gagal mendapatkan list kota";

    protected $table = 'kota';

    public function getList($page) {
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                    ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $cities = DB::table($this->table)
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->orderBy('id_provinsi')
                    ->orderBy('id')
                    ->get()
                    ->toArray();

            // Menghitung total page dari semua data yg bisa diperoleh
            $totalPage = $limit * $page != $dataCount ? $dataCount / $limit + 1 : $dataCount / $limit;
            
            // Menentukan apakah ada page selanjutnya
            $hasNext = count($cities) == $limit && $limit * $page != $dataCount;
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

            // Return jika ada data
            if (count($cities) > 0) {
                $newCity = array();
                foreach ($cities as $city) {
                    if ($city['id_provinsi'] != NULL) {
                        $city['provinsi'] = DB::table('provinsi')->where('id', $city['id_provinsi'])->first();
                    }
                    array_push($newCity, $city);
                }

                return array(self::CODE_SUCCESS, NULL, NULL, $newCity, $dataPage);
            }
            
            // Return jika tidak ada data
            return array(self::CODE_SUCCESS, NULL, NULL, $cities, $dataPage);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_GET_CITY_LIST_FAILED, $ex->getMessage(), NULL);
        }
    }

}

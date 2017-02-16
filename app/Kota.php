<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Kota extends BaseModel {
    
    const RESULT_GET_CITY_LIST_FAILED = "Gagal mendapatkan list kota";
    protected $table = 'kota';

    public function getList($page, $limit) {
        try {
            $allData = DB::table($this->table)
                    ->get()
                    ->toArray();

            $cities = DB::table($this->table)
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->orderBy('id_provinsi')
                    ->orderBy('id')
                    ->get()
                    ->toArray();

            if (count($cities) > 0) {
                $newCity = array();
                foreach ($cities as $city) {
                    if ($city['id_provinsi'] != NULL) {
                        $city['provinsi'] = DB::table('provinsi')->where('id', $city['id_provinsi'])->first();
                    }
                    array_push($newCity, $city);
                }
                $totalPage = $limit * $page != count($allData) ? count($allData) / $limit + 1 : count($allData) / $limit;
                $hasNext = count($cities) == $limit && $limit * $page != count($allData);
                if ($hasNext) {
                    $nextPage = $page + 1;
                } else {
                    $nextPage = -1;
                }
                return array(self::CODE_SUCCESS, NULL, NULL, $newCity, $hasNext, count($allData), intval($totalPage), $limit, $page, $nextPage);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_GET_CITY_LIST_FAILED, $ex->getMessage(), NULL);
        }
    }

}

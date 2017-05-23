<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use TATravel\Util\DataPage;

class Kota extends BaseModel
{

    const RESULT_GET_CITY_LIST_FAILED = "Gagal mendapatkan list kota";

    protected $table = 'kota';

    public function getList($provinceId)
    {
        try {
            $cities = DB::table($this->table)
                ->where('id_provinsi', $provinceId)
                ->get()
                ->toArray();
            return array(self::CODE_SUCCESS, NULL, NULL, $cities);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_GET_CITY_LIST_FAILED, $ex->getMessage(), NULL);
        }
    }

}

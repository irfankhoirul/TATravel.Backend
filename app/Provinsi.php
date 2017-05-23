<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Provinsi extends BaseModel
{
    const RESULT_GET_PROVINCE_LIST_FAILED = "Gagal mendapatkan list provinsi";

    protected $table = 'provinsi';

    public function getList()
    {
        try {
            $provinces = DB::table($this->table)
                ->get()
                ->toArray();

            return array(self::CODE_SUCCESS, NULL, NULL, $provinces);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESULT_GET_PROVINCE_LIST_FAILED, $ex->getMessage(), NULL);
        }
    }
}

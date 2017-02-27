<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\Util\DataPage;

class Lokasi extends BaseModel
{
    protected $table = 'lokasi';
    
    public function getList($operatorTravelId, $cityId, $page){
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                    ->where('id_kota', $cityId)
                    ->where('id_operator_travel', $operatorTravelId)
                    ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                    ->where('id_kota', $cityId)
                    ->where('id_operator_travel', $operatorTravelId)
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
}

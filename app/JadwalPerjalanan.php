<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\Util\DataPage;

class JadwalPerjalanan extends BaseModel {

    const STATUS_SCHEDULED = 'S';
    const STATUS_ON_THE_WAY = 'O';
    const STATUS_ARRIVED = 'A';
    const STATUS_CANCELLED = 'C';
    const STATUS_DELAYED = 'D';

    protected $table = 'jadwal_perjalanan';

    public function getList($operatorTravelId, $idDepartureLocation, $idDestinationLocation, $date, $page) {    
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            $date = date("Y-m-d", $date);
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                    ->where('id_operator_travel', $operatorTravelId)
                    ->where('id_lokasi_pemberangkatan', $idDepartureLocation)
                    ->where('id_lokasi_tujuan', $idDestinationLocation)
                    ->whereDate('waktu_keberangkatan', $date)
                    ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                    ->where('id_operator_travel', $operatorTravelId)
                    ->where('id_lokasi_pemberangkatan', $idDepartureLocation)
                    ->where('id_lokasi_tujuan', $idDestinationLocation)
                    ->whereDate('waktu_keberangkatan', $date)
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
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

    public function show($id) {
        try {
            $location = DB::table($this->table)
                    ->where('id', $id)
                    ->first();

            if ($location['id_lokasi_pemberangkatan'] != null && $location['id_lokasi_tujuan'] != null) {
                $location['lokasi_pemberangkatan'] = DB::table('lokasi')
                        ->where('id', $location['id_lokasi_pemberangkatan'])
                        ->first();

                $location['lokasi_tujuan'] = DB::table('lokasi')
                        ->where('id', $location['id_lokasi_tujuan'])
                        ->first();
            }

            return array(self::CODE_SUCCESS, NULL, NULL, $location);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

}

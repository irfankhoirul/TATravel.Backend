<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\Util\DataPage;

class OperatorTravel extends BaseModel {

    protected $table = 'operator_travel';

    public function listOperatorTravel($cityId, $page) {
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                    ->whereIn('id', function($query) use ($cityId) {
                        $query->select(DB::raw('operator_travel.id'))
                        ->from('operator_travel')
                        ->leftJoin('lokasi', 'lokasi.id_operator_travel', '=', 'operator_travel.id')
                        ->where('lokasi.id_kota', '=', $cityId);
                    })
                    ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                    ->whereIn('id', function($query) use ($cityId) {
                        $query->select(DB::raw('operator_travel.id'))
                        ->from('operator_travel')
                        ->leftJoin('lokasi', 'lokasi.id_operator_travel', '=', 'operator_travel.id')
                        ->where('lokasi.id_kota', '=', $cityId);
                    })
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
            $operatorTravel = DB::table($this->table)
                    ->where('id', '=', $id)
                    ->first();

            $operatorTravel['kota'] = DB::table('kota')
                    ->where('id', '=', $operatorTravel['id_kota'])
                    ->first();

            return array(self::CODE_SUCCESS, NULL, NULL, $operatorTravel);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

}

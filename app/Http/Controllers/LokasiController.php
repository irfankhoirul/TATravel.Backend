<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use Illuminate\Support\Facades\DB;
use TATravel\Lokasi;
use Validator;

class LokasiController extends BaseController {

    /**
     * Post Data :
     * - CityId : Required
     * - Page   : Required
     */
    public function getList($operatorTravelId, Request $request) {
        $validator = Validator::make($request->all(), [
                    'cityId' => 'required',
                    'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        } 

        $cityId = $request->request->get('cityId');
        $page = $request->request->get('page');

        $lokasi = new Lokasi();        
        list($status, $message, $technicalMessage, $datas, $dataPage) = $lokasi->getList($operatorTravelId, $cityId, $page);
        $this->returnJsonWithPagination($status, $message, $technicalMessage, $datas, $dataPage);
    }

    /** OLD
     * Memberikan list lokasi pemberangkatan dan tujuan dari operator travel
     * */
    public function availableLocation(Request $request) {
        $page = $request->request->get('page');
        $limit = $request->request->get('limit');
        $cityId = $request->request->get('cityId');

        $allData = DB::table('lokasi')
                ->where('id_kota', $cityId)
                ->orderBy('id')
                ->get()
                ->toArray();

        $locations = DB::table('lokasi')
                ->where('id_kota', $cityId)
                ->offset(($page - 1) * $limit)
                ->orderBy('id')
                ->limit($limit)
                ->get()
                ->toArray();

        if (count($locations) > 0) {
            $newLocations = array();
            foreach ($locations as $location) {
                if ($location['id_operator_travel'] != NULL) {
                    $location['operator_travel'] = DB::table('operator_travel')->where('id', $location['id_operator_travel'])->first();
                    array_push($newLocations, $location);
                }
            }

            $totalPage = $limit * $page != count($allData) ? count($allData) / $limit + 1 : count($allData) / $limit;
            $hasNext = count($locations) == $limit && $limit * $page != count($allData);
            if ($hasNext) {
                $nextPage = $page + 1;
            } else {
                $nextPage = -1;
            }
            self::renderResultWithPagination(1, "Berhasil", $newLocations, $hasNext, count($allData), intval($totalPage), $limit, $page, $nextPage);
        } else {
            self::renderResultWithPagination(0, "Tidak ada data", null, false);
        }
    }

}

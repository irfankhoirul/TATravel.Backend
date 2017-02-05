<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use Illuminate\Support\Facades\DB;

class KotaController extends Controller {

    public function availableCity(Request $request) {
        $page = $request->request->get('page');
        $limit = $request->request->get('limit');

        $allData = DB::table('kota')
                ->get()
                ->toArray();

        $cities = DB::table('kota')
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
            self::renderResultWithPagination(1, "Berhasil", $newCity, $hasNext, count($allData), intval($totalPage), $limit, $page, $nextPage);
        } else {
            self::renderResultWithPagination(0, "Tidak ada data", null, false);
        }
    }

}

<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\Util\DataPage;
use TATravel\Util\DistanceUtil;

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
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL, NULL);
        }
    }

    public function getDepartureAvailability($latitude, $longitude)
    {
        // Query list lokasi dari travel yang bersedia menjemput
        try {
            $operatorTravelLocations = DB::table('lokasi')
                ->leftJoin('operator_travel', 'operator_travel.id', '=', 'lokasi.id_operator_travel')
                ->where('operator_travel.izinkan_lokasi_khusus', '=', TRUE)
                ->select('lokasi.*', 'operator_travel.jarak_penjemputan_maksimum')
                ->get()
                ->toArray();

            $locations = array();
            $distanceUtil = new DistanceUtil();

            // foreach lokasi travel cek jarak lokasi travel dari lokasi user
            foreach ($operatorTravelLocations as $operatorTravelLocation) {
                if ($operatorTravelLocation['latitude'] != NULL && $operatorTravelLocation['longitude'] != NULL) {
                    $distance = $distanceUtil->getDistance((double)$latitude, (double)$longitude,
                        (double)$operatorTravelLocation['latitude'], (double)$operatorTravelLocation['longitude']);
                    if ($distance <= $operatorTravelLocation['jarak_penjemputan_maksimum']) {
                        $operatorTravelLocation['operator_travel'] = $this->show($operatorTravelLocation['id_operator_travel'])[3];
                        $operatorTravelLocation['distance'] = $distance;
                        array_push($locations, $operatorTravelLocation);
                    }
                }
            }

            return array(self::CODE_SUCCESS, NULL, NULL, $locations);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }


//        // Foreach travel, Query list lokasi travel by city (compare string)
//        // Foreach lokasi travel, Cek jarak lokasi travel dan lokasi user
//        $json = file_get_contents('http://maps.googleapis.com/maps/api/directions/json?origin=-7.290113046998566,112.79972579330206&destination=-7.290113046998566,112.70072579330206');
//        print_r("\n0: " . $json);
//        $json1 = file_get_contents('http://maps.googleapis.com/maps/api/directions/json?origin=-7.290113046998566,112.79972579330206&destination=-7.290113046998566,112.70072579330206');
//        print_r("\n\n1: " . $json1);
//        $json2 = file_get_contents('http://maps.googleapis.com/maps/api/directions/json?origin=-7.290113046998566,112.79972579330206&destination=-7.290113046998566,112.70072579330206');
//        print_r("\n\n2: " . $json2);die;
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

    public function getDestinationAvailability($latitude, $longitude, $idOperatorTravel)
    {
        // Query list lokasi dari travel yang bersedia menjemput
        try {
            $operatorTravelLocations = DB::table('lokasi')
                ->leftJoin('operator_travel', 'operator_travel.id', '=', 'lokasi.id_operator_travel')
                ->where('operator_travel.izinkan_lokasi_khusus', '=', TRUE)
                ->where('operator_travel.id', '=', $idOperatorTravel)
                ->select('lokasi.*', 'operator_travel.jarak_penjemputan_maksimum')
                ->get()
                ->toArray();

            $locations = array();
            $distanceUtil = new DistanceUtil();

            // foreach lokasi travel cek jarak lokasi travel dari lokasi user
            foreach ($operatorTravelLocations as $operatorTravelLocation) {
                if ($operatorTravelLocation['latitude'] != NULL && $operatorTravelLocation['longitude'] != NULL) {
                    $distance = $distanceUtil->getDistance((double)$latitude, (double)$longitude,
                        (double)$operatorTravelLocation['latitude'], (double)$operatorTravelLocation['longitude']);
                    if ($distance <= $operatorTravelLocation['jarak_penjemputan_maksimum']) {
                        $operatorTravelLocation['operator_travel'] = $this->show($operatorTravelLocation['id_operator_travel'])[3];
                        array_push($locations, $operatorTravelLocation);
                    }
                }
            }

            return array(self::CODE_SUCCESS, NULL, NULL, $locations);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

}

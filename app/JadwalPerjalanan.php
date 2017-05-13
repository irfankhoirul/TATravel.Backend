<?php

namespace TATravel;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use TATravel\Util\DataPage;
use TATravel\Util\DistanceUtil;

class JadwalPerjalanan extends BaseModel
{

    const STATUS_SCHEDULED = 'S';
    const STATUS_ON_THE_WAY = 'O';
    const STATUS_ARRIVED = 'A';
    const STATUS_CANCELLED = 'C';
    const STATUS_DELAYED = 'D';

    protected $table = 'jadwal_perjalanan';

    public function getList($operatorTravelId,
                            $idDepartureLocations,
                            $departureLatitude,
                            $departureLongitude,
                            $idDestinationLocations,
                            $destinationLatitude,
                            $destinationLongitude,
                            $idPassengers,
                            $date,
                            $page)
    {
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            $date = strtotime($date);
            $date = date("Y-m-d", $date);
            // Count semua data, jika ada kriteria tertentu, masukkan disini

            // Cek apakah ada pemberangkatan dari list ketersediaan departure dengan tujuan salah satu dari list ketersediaan destination
            // Tampilkan hasil query
            $dataCount = DB::table($this->table)
                ->where('id_operator_travel', $operatorTravelId)
                ->whereIn('id_lokasi_pemberangkatan', json_decode($idDepartureLocations, TRUE))
                ->whereIn('id_lokasi_tujuan', json_decode($idDestinationLocations, TRUE))
                ->where('jumlah_kursi_tersedia', '>=', sizeof(json_decode($idPassengers, TRUE)))
                ->whereDate('tanggal_keberangkatan', $date)
                ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                ->where('id_operator_travel', $operatorTravelId)
                ->whereIn('id_lokasi_pemberangkatan', json_decode($idDepartureLocations, TRUE))
                ->whereIn('id_lokasi_tujuan', json_decode($idDestinationLocations, TRUE))
                ->where('jumlah_kursi_tersedia', '>=', sizeof(json_decode($idPassengers, TRUE)))
                ->whereDate('tanggal_keberangkatan', $date)
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get()
                ->toArray();

            $distanceUtil = new DistanceUtil();
            for ($i = 0; $i < count($datas); $i++) {
                $datas[$i]['waktu_keberangkatan'] = date("d - m - Y H:i", strtotime($datas[$i]['waktu_keberangkatan']));
                $datas[$i]['waktu_kedatangan'] = date("d - m - Y H:i", strtotime($datas[$i]['waktu_kedatangan']));

                $datas[$i]['lokasi_pemberangkatan'] = DB::table('lokasi')
                    ->where('id', $datas[$i]['id_lokasi_pemberangkatan'])
                    ->first();

                $datas[$i]['jarakPenjemputan'] = $distanceUtil->getDistance(
                    (double)$departureLatitude, (double)$departureLongitude,
                    (double)$datas[$i]['lokasi_pemberangkatan']['latitude'], (double)$datas[$i]['lokasi_pemberangkatan']['longitude']);

                $datas[$i]['lokasi_pemberangkatan']['kota'] = DB::table('kota')
                    ->where('id', $datas[$i]['lokasi_pemberangkatan']['id_kota'])
                    ->first();

                $datas[$i]['lokasi_tujuan'] = DB::table('lokasi')
                    ->where('id', $datas[$i]['id_lokasi_tujuan'])
                    ->first();

                $datas[$i]['jarakPengantaran'] = $distanceUtil->getDistance(
                    (double)$destinationLatitude, (double)$destinationLongitude,
                    (double)$datas[$i]['lokasi_tujuan']['latitude'], (double)$datas[$i]['lokasi_tujuan']['longitude']);

                $datas[$i]['lokasi_tujuan']['kota'] = DB::table('kota')
                    ->where('id', $datas[$i]['lokasi_tujuan']['id_kota'])
                    ->first();

//                $address = "http://maps.googleapis.com/maps/api/directions/json?".
//                    "origin=".$departureLatitude.",".$departureLongitude.
//                    "&destination=".$datas[$i]['lokasi_pemberangkatan']['latitude'].",".$datas[$i]['lokasi_pemberangkatan']['latitude']."";
//                print_r($address);die;
//                $json = file_get_contents($address);
//                print_r($json);die;
            }

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

    public function getDriverScheduleList($userId /* untuk driver */, $page)
    {
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                ->join('supir', 'supir.id', '=', 'jadwal_perjalanan.id_supir')
                ->where('supir.id_user', $userId)
                ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                ->join('supir', 'supir.id', '=', 'jadwal_perjalanan.id_supir')
                ->where('supir.id_user', $userId)
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

            $completeDatas = array();
            foreach ($datas as $data) {
                $data['operator_travel'] = DB::table('operator_travel')
                    ->where('id', $data['id_operator_travel'])
                    ->first();
                $data['lokasi_pemberangkatan'] = DB::table('lokasi')
                    ->where('id', $data['id_lokasi_pemberangkatan'])
                    ->first();
                $data['lokasi_tujuan'] = DB::table('lokasi')
                    ->where('id', $data['id_lokasi_tujuan'])
                    ->first();
                $data['mobil'] = DB::table('mobil')
                    ->where('id', $data['id_mobil'])
                    ->first();

                array_push($completeDatas, $data);
            }

            // Return data
            return array(self::CODE_SUCCESS, NULL, NULL, $completeDatas, $dataPage);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL, NULL);
        }
    }

    public function show($id)
    {
        try {
            $location = DB::table($this->table)
                ->where('id', $id)
                ->first();

            if ($location != NULL) {
                $location['lokasi_pemberangkatan'] = DB::table('lokasi')
                    ->where('id', $location['id_lokasi_pemberangkatan'])
                    ->first();

                $location['lokasi_tujuan'] = DB::table('lokasi')
                    ->where('id', $location['id_lokasi_tujuan'])
                    ->first();

                $location['operator_travel'] = DB::table('operator_travel')
                    ->where('id', $location['id_operator_travel'])
                    ->first();

                $location['mobil'] = DB::table('mobil')
                    ->where('id', $location['id_mobil'])
                    ->first();
            }

            return array(self::CODE_SUCCESS, NULL, NULL, $location);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

    // Apakah supir ybs adalah supir jadwl perjalanan tsb
    public function isDriver($idSchedule, $userId)
    {
        try {
            $schedule = DB::table($this->table)
                ->join('supir', 'supir.id', '=', 'jadwal_perjalanan.id_supir')
                ->where('supir.id_user', $userId)
                ->where('jadwal_perjalanan.id', $idSchedule)
                ->first();

            if ($schedule != NULL) {
                return true;
            }
            return false;
        } catch (QueryException $ex) {
            return false;
        }
    }

    public function setStatus($idSchedule, $status)
    {
        try {
            $schedule = DB::table($this->table)
                ->where('id', $idSchedule)
                ->first();

            if ($schedule != NULL) {
                DB::table($this->table)
                    ->where('id', $idSchedule)
                    ->update(['status' => $status]);

                return array(self::CODE_SUCCESS, "Berhasil mengubah status perjalanan", NULL, NULL);
            }
            return array(self::CODE_ERROR, "Data tidak ditemukan", NULL, NULL);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

}

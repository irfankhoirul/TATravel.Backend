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
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL, NULL);
        }
    }

    public function getDriverScheduleList($userId /* untuk driver */, $page) {
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

    public function show($id) {
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
    public function isDriver($idSchedule, $userId) {
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

    public function setStatus($idSchedule, $status) {
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

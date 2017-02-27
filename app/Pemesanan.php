<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\Util\DataPage;

class Pemesanan extends BaseModel {

    protected $table = 'pemesanan';

    public function reservation($idUser, $idJadwalPerjalanan) {
        $reservationCode = "RES-" . time() . rand(100, 999);
        try {
            $id = DB::table($this->table)->insertGetId(
                    ['id_user' => $idUser,
                        'id_jadwal_perjalanan' => $idJadwalPerjalanan,
                        'kode_pemesanan' => $reservationCode
                    ]
            );
            return array(self::CODE_SUCCESS, NULL, $id);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

    public function isBookerUser($userId, $scheduleId) {
        try {
            $reservation = DB::table($this->table)
                    ->where('id_user', $userId)
                    ->where('id_jadwal_perjalanan', $scheduleId)
                    ->first();

            if ($reservation != NULL) {
                return TRUE;
            } else {
                return FALSE;
            }
        } catch (QueryException $ex) {
            return FALSE;
        }
    }

    public function show($id) {
        try {
            $reservation = DB::table($this->table)
                    ->where('id', $id)
                    ->first();
            if ($reservation != NULL) {
                $reservation['jadwal_perjalanan'] = DB::table('jadwal_perjalanan')
                        ->where('id', $reservation['id_jadwal_perjalanan'])
                        ->first();
                $reservation['jadwal_perjalanan']['operator_travel'] = DB::table('operator_travel')
                        ->where('id', $reservation['jadwal_perjalanan']['id_operator_travel'])
                        ->first();
                $reservation['pembayaran'] = DB::table('pembayaran')
                        ->where('id_pemesanan', $id)
                        ->first();
                return array(self::CODE_SUCCESS, NULL, NULL, $reservation);
            } else {
                return array(self::CODE_ERROR, "Tidak ada data", NULL, NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

    public function getList($userId, $status, $page) {
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            if ($status == NULL) {
                $status = [
                    JadwalPerjalanan::STATUS_SCHEDULED,
                    JadwalPerjalanan::STATUS_ON_THE_WAY,
                    JadwalPerjalanan::STATUS_ARRIVED,
                    JadwalPerjalanan::STATUS_CANCELLED,
                    JadwalPerjalanan::STATUS_DELAYED,
                ];
            }
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                    ->where('id_user', $userId)
                    ->join('jadwal_perjalanan', 'jadwal_perjalanan.id', '=', 'pemesanan.id_jadwal_perjalanan')
                    ->whereIn('jadwal_perjalanan.status', $status)
                    ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                    ->where('id_user', $userId)
                    ->join('jadwal_perjalanan', 'jadwal_perjalanan.id', '=', 'pemesanan.id_jadwal_perjalanan')
                    ->whereIn('jadwal_perjalanan.status', $status)
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

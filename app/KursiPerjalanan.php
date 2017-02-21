<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class KursiPerjalanan extends BaseModel {

    const STATUS_AVAILABLE = 'A';
    const STATUS_BOOKED = 'B';
    const STATUS_UNAVAILABLE = 'U';
    const BOOKING_TIME_LIMIT = 300; // Detik

    protected $table = 'kursi_perjalanan';

    public function getList($idJadwalPerjalanan) {
        try {
            $seats = DB::table($this->table)
                    ->where('id_jadwal_perjalanan', $idJadwalPerjalanan)
                    ->get();

            return array(self::CODE_SUCCESS, NULL, NULL, $seats);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

    public function bookSeat($idKursi) {
        try {
            $seat = DB::table($this->table)
                    ->select("kursi_perjalanan.*")
                    ->addSelect("kursi_mobil.nomor")
                    ->where('kursi_perjalanan.id', $idKursi)
                    ->join('kursi_mobil', 'kursi_mobil.id', '=', 'kursi_perjalanan.id_kursi_mobil')
                    ->first();

            // RULEs :
            // 
            // Booking -> diberi waktu 5 menit untuk melakukan order
            // Jika lebih dari 5 menit tidak menyelesaikan order, user lain bisa melakukan booking pada kusi tersebut -> Booking kursi harus diulang
            // 
            // Order -> diberi waktu 5 jam untuk melakukan pembayaran
            // Jika lebih dari 5 jam tidak melakukan pembayaran, user lain bisa melakukan booking pada kusi tersebut -> Order harus diulang
            // 
            // 
            // Cek jika kursi masih available
            $updateTime = strtotime($seat['updated_at']);
            $now = new \DateTime(null, new \DateTimeZone('Asia/Jakarta'));
            $nowMicro = strtotime($now->format('m/d/Y H:i:s'));
            $bookTimeDifference = $nowMicro - $updateTime;

            if ($seat['status'] == self::STATUS_AVAILABLE ||
                    ($seat['updated_at'] != NULL && $bookTimeDifference > self::BOOKING_TIME_LIMIT)) {
                DB::table($this->table)
                        ->where('id', $idKursi)
                        ->update(['status' => self::STATUS_BOOKED]);

                return array(self::CODE_SUCCESS, "Anda memilih kursi " . $seat['nomor'], NULL);
            } else {
                return array(self::CODE_ERROR, "Kursi tidak tersedia", NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

}

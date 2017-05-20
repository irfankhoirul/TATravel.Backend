<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class KursiPerjalanan extends BaseModel
{

    const STATUS_AVAILABLE = 'A';
    const STATUS_BOOKED = 'B';
    const STATUS_UNAVAILABLE = 'U';
    const STATUS_SOLD = 'S';

    const BOOKING_TIME_LIMIT = 300; // Detik (5 Menit)
    const PAYMENT_TIME_LIMIT = 18000; // Detik (5 Jam)

    protected $table = 'kursi_perjalanan';

    public function getList($idJadwalPerjalanan)
    {
        try {
            $seats = DB::table($this->table)
                ->where('id_jadwal_perjalanan', $idJadwalPerjalanan)
                ->groupBy('id')
                ->orderBy('id', 'ASC')
                ->get();

            $seats = json_decode(json_encode($seats), true);

            for ($i = 0; $i < count($seats); $i++) {
                $updateTime = strtotime($seats[$i]['updated_at']);
                $now = new \DateTime(null, new \DateTimeZone('Asia/Jakarta'));
                $nowMicro = strtotime($now->format('m/d/Y H:i:s'));
                $bookTimeDifference = $nowMicro - $updateTime;

                if (($seats[$i]['status'] == self::STATUS_BOOKED && $bookTimeDifference > self::BOOKING_TIME_LIMIT) ||
                    ($seats[$i]['status'] == self::STATUS_UNAVAILABLE && $bookTimeDifference > self::PAYMENT_TIME_LIMIT)
                ) {
                    $seats[$i]['status'] = self::STATUS_AVAILABLE;
                }

                $seats[$i]['kursi_mobil'] = DB::table('kursi_mobil')
                    ->where('id', $seats[$i]['id_kursi_mobil'])
                    ->first();
            }

            return array(self::CODE_SUCCESS, NULL, NULL, $seats);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

    public function bookSeat($seatIds)
    {
        try {
            $seat = DB::table($this->table)
                ->select("kursi_perjalanan.*")
                ->addSelect("kursi_mobil.nomor")
//                ->where('kursi_perjalanan.id', $seatIds)
                ->whereIn('kursi_perjalanan.id', json_decode($seatIds, TRUE))
                ->join('kursi_mobil', 'kursi_mobil.id', '=', 'kursi_perjalanan.id_kursi_mobil')
                ->get();

            // RULEs :
            // 
            // Booking -> diberi waktu 10 menit untuk melakukan order
            // Jika lebih dari 10 menit tidak menyelesaikan order, user lain bisa melakukan booking pada kusi tersebut -> Booking kursi harus diulang
            // 
            // Order -> diberi waktu 5 jam untuk melakukan pembayaran
            // Jika lebih dari 5 jam tidak melakukan pembayaran, user lain bisa melakukan booking pada kusi tersebut -> Order harus diulang
            // 
            // 
            // Cek jika kursi masih available
            $counterAvailable = 0;
            foreach ($seat as $item) {
                $updateTime = strtotime($item['updated_at']);
                $now = new \DateTime(null, new \DateTimeZone('Asia/Jakarta'));
                $nowMicro = strtotime($now->format('m/d/Y H:i:s'));
                $bookTimeDifference = $nowMicro - $updateTime;

                if ($item['status'] == self::STATUS_AVAILABLE ||
                    ($item['updated_at'] != NULL && $bookTimeDifference > self::BOOKING_TIME_LIMIT)
                ) {
                    $counterAvailable++;
                } else {
                    return array(self::CODE_ERROR, "Gagal memilih kursi", NULL);
                }
            }

            if ($counterAvailable == count($seat)) {
                $counter = 0;
                foreach ($seat as $item) {
                    $updateTime = strtotime($item['updated_at']);
                    $now = new \DateTime(null, new \DateTimeZone('Asia/Jakarta'));
                    $nowMicro = strtotime($now->format('m/d/Y H:i:s'));
                    $bookTimeDifference = $nowMicro - $updateTime;

                    if ($item['status'] == self::STATUS_AVAILABLE ||
                        ($item['updated_at'] != NULL && $bookTimeDifference > self::BOOKING_TIME_LIMIT)
                    ) {
                        DB::table($this->table)
                            ->where('id', $item['id'])
                            ->update(['status' => self::STATUS_BOOKED]);

                        $counter++;
                    } else {
                        return array(self::CODE_ERROR, "Gagal memilih kursi", NULL);
                    }
                }
                return array(self::CODE_SUCCESS, NULL, NULL);
            } else {
                return array(self::CODE_ERROR, "Kursi yang anda pilih tidak tersedia", NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

}

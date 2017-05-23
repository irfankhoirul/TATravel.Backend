<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use TATravel\Util\DataPage;

class Pemesanan extends BaseModel
{

    protected $table = 'pemesanan';

    public function reservation($idUser, $idJadwalPerjalanan, $passengerIds, $seatIds,
                                $pickUpLat, $pickUpLon, $pickUpAddress, $takeLat, $takeLon, $takeAddress)
    {
        $reservationCode = "RES-" . time() . rand(100, 999);
        try {
            $id = DB::table($this->table)->insertGetId(
                ['id_user' => $idUser,
                    'id_jadwal_perjalanan' => $idJadwalPerjalanan,
                    'kode_pemesanan' => $reservationCode
                ]
            );

            // Create data penumpang perjalanan
            $seatIds = json_decode($seatIds, TRUE);
            $passengerIds = json_decode($passengerIds, TRUE);
            for ($i = 0; $i < count($passengerIds); $i++) {

                $idPenumpangPerjalanan = DB::table('penumpang_perjalanan')->insertGetId(
                    ['id_pemesanan' => $id,
                        'id_penumpang' => $passengerIds[$i]
                    ]
                );

                DB::table('kursi_perjalanan')
                    ->where('id', $seatIds[$i])
                    ->update(['status' => KursiPerjalanan::STATUS_UNAVAILABLE, 'id_penumpang_perjalanan' => $idPenumpangPerjalanan]);

                // Insert data lokasi penjemputan
                $idPickUpLocation = DB::table('lokasi_detail')->insertGetId(
                    ['id_penumpang_perjalanan' => $idPenumpangPerjalanan,
                        'tipe' => 'P',
                        'alamat' => $pickUpAddress,
                        'latitude' => $pickUpLat,
                        'longitude' => $pickUpLon,
                        'id_jadwal_perjalanan' => $idJadwalPerjalanan,
                        'id_pemesanan' => $id
                    ]
                );

                // Insert data lokasi pengantaran
                $idTakeLocation = DB::table('lokasi_detail')->insertGetId(
                    ['id_penumpang_perjalanan' => $idPenumpangPerjalanan,
                        'tipe' => 'T',
                        'alamat' => $takeAddress,
                        'latitude' => $takeLat,
                        'longitude' => $takeLon,
                        'id_jadwal_perjalanan' => $idJadwalPerjalanan,
                        'id_pemesanan' => $id
                    ]
                );
            }

            return array(self::CODE_SUCCESS, NULL, $id);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage());
        }
    }

    public function isBookerUser($userId, $scheduleId)
    {
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

    public function show($id)
    {
        try {
            $reservation = DB::table($this->table)
                ->where('id', $id)
                ->first();
            if ($reservation != NULL) {
                $jadwalPerjalanan = new JadwalPerjalanan();

                $reservation['user'] = DB::table('user')
                    ->where('id', $reservation['id_user'])
                    ->first();
                $reservation['jadwal_perjalanan'] = $jadwalPerjalanan->show($reservation['id_jadwal_perjalanan'])[3];
                $reservation['pembayaran'] = DB::table('pembayaran')
                    ->where('id_pemesanan', $reservation['id'])
                    ->first();
                $reservation['lokasi_penjemputan'] = DB::table('lokasi_detail')
                    ->where('tipe', 'P')
                    ->where('id_jadwal_perjalanan', $reservation['jadwal_perjalanan']['id'])
                    ->where('id_pemesanan', $reservation['id'])
                    ->first();
                $reservation['lokasi_pengantaran'] = DB::table('lokasi_detail')
                    ->where('tipe', 'T')
                    ->where('id_jadwal_perjalanan', $reservation['jadwal_perjalanan']['id'])
                    ->where('id_pemesanan', $reservation['id'])
                    ->first();

                $tmpPenumpangPerjalanan = DB::table('penumpang_perjalanan')
                    ->where('id_pemesanan', $reservation['id'])
                    ->get();
                $reservation['penumpang_perjalanan'] = json_decode(json_encode($tmpPenumpangPerjalanan), true);
                for ($j = 0; $j < count($reservation['penumpang_perjalanan']); $j++) {
                    $tmpPenumpang = DB::table('penumpang')
                        ->where('id', $reservation['penumpang_perjalanan'][$j]['id_penumpang'])
                        ->first();

                    $reservation['penumpang_perjalanan'][$j]['penumpang'] = json_decode(json_encode($tmpPenumpang), true);

                    $tmpKursiPerjalanan = DB::table('kursi_perjalanan')
                        ->where('id_penumpang_perjalanan', $reservation['penumpang_perjalanan'][$j]['id'])
                        ->first();

                    $reservation['penumpang_perjalanan'][$j]['kursi_perjalanan'] = json_decode(json_encode($tmpKursiPerjalanan), true);
                    $reservation['penumpang_perjalanan'][$j]['kursi_perjalanan']['kursi_mobil'] = DB::table('kursi_mobil')
                        ->where('id', $reservation['penumpang_perjalanan'][$j]['kursi_perjalanan']['id_kursi_mobil'])
                        ->first();
                }

                return array(self::CODE_SUCCESS, NULL, NULL, $reservation);
            } else {
                return array(self::CODE_ERROR, "Tidak ada data", NULL, NULL);
            }
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, NULL, $ex->getMessage(), NULL);
        }
    }

    public function getList($userId, /*$status,*/
                            $page)
    {
        $limit = config('constant.DATA_PAGE_QUERY_LIMIT');
        try {
            // Count semua data, jika ada kriteria tertentu, masukkan disini
            $dataCount = DB::table($this->table)
                ->where('id_user', $userId)
                ->count();

            // Get data sejumlah limit, jika ada kriteria tertentu, masukkan disini
            $datas = DB::table($this->table)
                ->where('id_user', $userId)
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->get()
                ->toArray();

            $jadwalPerjalanan = new JadwalPerjalanan();
            for ($i = 0; $i < count($datas); $i++) {
                $datas[$i]['user'] = DB::table('user')
                    ->where('id', $datas[$i]['id_user'])
                    ->first();
                $datas[$i]['jadwal_perjalanan'] = $jadwalPerjalanan->show($datas[$i]['id_jadwal_perjalanan'])[3];
                $tmpPembayaran = DB::table('pembayaran')
                    ->where('id_pemesanan', $datas[$i]['id'])
                    ->first();
                $datas[$i]['pembayaran'] = json_decode(json_encode($tmpPembayaran), true);
                $datas[$i]['lokasi_penjemputan'] = DB::table('lokasi_detail')
                    ->where('tipe', 'P')
                    ->where('id_jadwal_perjalanan', $datas[$i]['jadwal_perjalanan']['id'])
                    ->where('id_pemesanan', $datas[$i]['id'])
                    ->first();
                $datas[$i]['lokasi_pengantaran'] = DB::table('lokasi_detail')
                    ->where('tipe', 'T')
                    ->where('id_jadwal_perjalanan', $datas[$i]['jadwal_perjalanan']['id'])
                    ->where('id_pemesanan', $datas[$i]['id'])
                    ->first();

                $tmpPenumpangPerjalanan = DB::table('penumpang_perjalanan')
                    ->where('id_pemesanan', $datas[$i]['id'])
                    ->get();
                $datas[$i]['penumpang_perjalanan'] = json_decode(json_encode($tmpPenumpangPerjalanan), true);
                $statusPembayaran = $datas[$i]['pembayaran']['status'];
                for ($j = 0; $j < count($datas[$i]['penumpang_perjalanan']); $j++) {
                    $tmpPenumpang = DB::table('penumpang')
                        ->where('id', $datas[$i]['penumpang_perjalanan'][$j]['id_penumpang'])
                        ->first();

                    $datas[$i]['penumpang_perjalanan'][$j]['penumpang'] = json_decode(json_encode($tmpPenumpang), true);

                    $tmpKursiPerjalanan = DB::table('kursi_perjalanan')
                        ->where('id_penumpang_perjalanan', $datas[$i]['penumpang_perjalanan'][$j]['id'])
                        ->first();

                    $datas[$i]['penumpang_perjalanan'][$j]['kursi_perjalanan'] = json_decode(json_encode($tmpKursiPerjalanan), true);

                    $tmpKursiPerjalanan = $datas[$i]['penumpang_perjalanan'][$j]['kursi_perjalanan'];

                    $updateTime = strtotime($tmpKursiPerjalanan['updated_at']);
                    $now = new \DateTime(null, new \DateTimeZone('Asia/Jakarta'));
                    $nowMicro = strtotime($now->format('m/d/Y H:i:s'));
                    $bookTimeDifference = $nowMicro - $updateTime;

                    if ($tmpKursiPerjalanan['status'] == KursiPerjalanan::STATUS_UNAVAILABLE ||
                        ($tmpKursiPerjalanan['updated_at'] != NULL && $bookTimeDifference > KursiPerjalanan::PAYMENT_TIME_LIMIT)
                    ) {
                        $statusPembayaran = Pembayaran::PAYMENT_STATUS_TIMEOUT;
                    }
                    $datas[$i]['pembayaran']['status'] = $statusPembayaran;

                    $datas[$i]['penumpang_perjalanan'][$j]['kursi_perjalanan']['kursi_mobil'] = DB::table('kursi_mobil')
                        ->where('id', $datas[$i]['penumpang_perjalanan'][$j]['kursi_perjalanan']['id_kursi_mobil'])
                        ->first();
                }
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

}

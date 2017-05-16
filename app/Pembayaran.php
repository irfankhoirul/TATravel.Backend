<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class Pembayaran extends BaseModel {

    const PAYMENT_STATUS_PAID = 'P';
    const PAYMENT_STATUS_UNPAID = 'U';
    const PAYMENT_STATUS_TIMEOUT = 'O';
    const RESERVATION_SUCCESS = 'Berhasil melakukan pemesanan';
    const RESERATION_FAILED = 'Gagal melakukan pemesanan';
    protected $table = 'pembayaran';

    public function reservation($reservationId) {
        $paymentCode = rand(10, 99) . time() . rand(10, 99);
        try {
            $id = DB::table($this->table)->insertGetId(
                    ['id_pemesanan' => $reservationId,
                        'kode_pembayaran' => $paymentCode,
                        'status' => self::PAYMENT_STATUS_UNPAID
                    ]
            );
            return array(self::CODE_SUCCESS, self::RESERVATION_SUCCESS, $id);
        } catch (QueryException $ex) {
            return array(self::CODE_ERROR, self::RESERATION_FAILED, $ex->getMessage());
        }
    }

}

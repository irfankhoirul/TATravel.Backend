<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\UserTravel;
use TATravel\Pemesanan;
use TATravel\Pembayaran;
use Validator;

class PemesananController extends BaseController
{

    /**
     * Proses :
     * - Buat record pemesanan
     * - Buat record pembayaran
     *
     * Post Data :
     * - IdJadwalPerjalanan
     */
    public function reservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idJadwalPerjalanan' => 'required|integer|min:1',
            'passengerIds' => 'required',
            'seatIds' => 'required',
            'pickUpLat' => 'required',
            'pickUpLon' => 'required',
            'pickUpAddress' => 'required',
            'takeLat' => 'required',
            'takeLon' => 'required',
            'takeAddress' => 'required'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        $idJadwalPerjalanan = $request->request->get('idJadwalPerjalanan');
        $passengerIds = $request->request->get('passengerIds');
        $seatIds = $request->request->get('seatIds');
        $pickUpLat = $request->request->get('pickUpLat');
        $pickUpLon = $request->request->get('pickUpLon');
        $pickUpAddress = $request->request->get('pickUpAddress');
        $takeLat = $request->request->get('takeLat');
        $takeLon = $request->request->get('takeLon');
        $takeAddress = $request->request->get('takeAddress');

        $reservation = new Pemesanan();
        list($status, $message, $reservationId) = $reservation->reservation($data['id'], $idJadwalPerjalanan, $passengerIds, $seatIds,
            $pickUpLat, $pickUpLon, $pickUpAddress, $takeLat, $takeLon, $takeAddress);
        if ($status == self::CODE_SUCCESS) {
            $payment = new Pembayaran();
            list($status, $message, $paymentId) = $payment->reservation($reservationId);
            $technicalMessage = $paymentId;
        }
        $this->returnJsonArray($status, $message, $technicalMessage, NULL);
    }

    /**
     * Post Data :
     * - IdJadwalPerjalanan
     */
    public function show($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idJadwalPerjalanan' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        $reservation = new Pemesanan();
        $isBookerUser = $reservation->isBookerUser($data['id'], $request->request->get('idJadwalPerjalanan'));
        if ($isBookerUser) {
            list($status, $message, $technicalMessage, $data) = $reservation->show($id);
            $this->returnJson($status, $message, $technicalMessage, $data);
        }
        $this->returnJsonErrorNoAccess();
    }

    /**
     * Post Data :
     * - Status : Optional // Status jadwal perjalanan
     * - Page   : Required
     */
    public function getList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'max:1',
            'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        $userId = $data['id'];
        $statusSchedule = [$request->request->get('status')]; // Array
        $page = $request->request->get('page');

        $reservation = new Pemesanan();
        list($status, $message, $technicalMessage, $datas, $dataPage) = $reservation->getList($userId, $statusSchedule, $page);
        $this->returnJsonWithPagination($status, $message, $technicalMessage, $datas, $dataPage);
    }

}

<?php

namespace TATravel\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\KursiPerjalanan;

class KursiPerjalananController extends BaseController {

    public function getList($id) {
        $kursiPerjalanan = new KursiPerjalanan();
        list($status, $message, $technicalMessage, $datas) = $kursiPerjalanan->getList($id);
        $this->returnJsonArray($status, $message, $technicalMessage, $datas);
    }

    public function bookSeat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seatIds' => 'required'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $seatIds = $request->request->get('seatIds');

        $kursiPerjalanan = new KursiPerjalanan();
        list($status, $message, $technicalMessage) = $kursiPerjalanan->bookSeat($seatIds);
        $this->returnJson($status, $message, $technicalMessage, NULL);
    }

}

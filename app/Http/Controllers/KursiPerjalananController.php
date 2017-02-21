<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\KursiPerjalanan;

class KursiPerjalananController extends BaseController {

    public function getList($id) {
        $kursiPerjalanan = new KursiPerjalanan();
        list($status, $message, $technicalMessage, $data) = $kursiPerjalanan->getList($id);
        $this->returnJson($status, $message, $technicalMessage, $data);
    }

    public function bookSeat($id) {
        $kursiPerjalanan = new KursiPerjalanan();
        list($status, $message, $technicalMessage) = $kursiPerjalanan->bookSeat($id);
        $this->returnJson($status, $message, $technicalMessage, NULL);
    }

}

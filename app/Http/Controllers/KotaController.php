<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use Validator;
use TATravel\Kota;

class KotaController extends BaseController {

    public function getList($id)
    {
        $kota = new Kota();
        list($code, $message, $technicalMessage, $cities) = $kota->getList($id);
        $this->returnJsonArray($code, $message, $technicalMessage, $cities);
    }

}

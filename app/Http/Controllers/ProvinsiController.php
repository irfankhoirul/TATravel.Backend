<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;

use TATravel\Http\Requests;
use TATravel\Provinsi;

class ProvinsiController extends BaseController
{
    public function getList()
    {
        $province = new Provinsi();
        list($code, $message, $technicalMessage, $provinces) = $province->getList();
        $this->returnJsonArray($code, $message, $technicalMessage, $provinces);
    }
}

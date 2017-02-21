<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use Validator;
use TATravel\Kota;

class KotaController extends BaseController {

    public function getList(Request $request) {
        $validator = Validator::make($request->all(), [
                    'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $page = $request->request->get('page');

        $kota = new Kota();
        list($code, $message, $technicalMessage, $cities, $dataPage) = $kota->getList($page);
        $this->returnJsonWithPagination($code, $message, $technicalMessage, $cities, $dataPage);
    }

}

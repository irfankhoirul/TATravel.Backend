<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use Validator;
use TATravel\Kota;

class KotaController extends BaseController {

    public function getList(Request $request) {
        $validator = Validator::make($request->all(), [
                    'page' => 'required',
                    'limit' => 'required'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }


        $page = $request->request->get('page');
        $limit = $request->request->get('limit');

        $kota = new Kota();
        list($code, $message, $technicalMessage, $cities, $hasNext, $countData, $totalPage, $limit, $page, $nextPage) = $kota->getList($page, $limit);
        $this->returnJsonWithPagination($code, $message, $technicalMessage, $cities, $hasNext, $countData, $totalPage, $limit, $page, $nextPage);
    }

}

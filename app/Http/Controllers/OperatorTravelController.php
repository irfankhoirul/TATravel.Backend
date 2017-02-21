<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use Illuminate\Support\Facades\DB;
use Validator;
use TATravel\OperatorTravel;

class OperatorTravelController extends BaseController {

    /**
     * Post Data :
     * - CityId     : Required
     * - Page       : Required
     */
    public function getList(Request $request) {
        $validator = Validator::make($request->all(), [
                    'cityId' => 'required|integer|min:1',
                    'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $page = $request->request->get('page');
        $cityId = $request->request->get('cityId');

        $operatorTravel = new OperatorTravel();
        list($status, $message, $technicalMessage, $datas, $dataPage) = $operatorTravel->listOperatorTravel($cityId, $page);
        $this->returnJsonWithPagination($status, $message, $technicalMessage, $datas, $dataPage);
    }

    public function show($id) {
        $operatorTravel = new OperatorTravel();
        list($status, $message, $technicalMessage, $data) = $operatorTravel->show($id);
        $this->returnJson($status, $message, $technicalMessage, $data);
    }

}

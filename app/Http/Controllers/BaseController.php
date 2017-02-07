<?php

namespace TATravel\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BaseController extends Controller {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected function returnJson($code, $message, $technicalMessage, $data) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['technicalMessage'] = $technicalMessage;
        if (is_array($data)) {
            $result['datas'] = $data;
        } else {
            $result['data'] = $data;
        }

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
    }

    protected function returnJsonWithPagination($code, $message, $technicalMessage, $data, $hasNext, $totalData, $totalPage, $limit, $currentPage, $nextPage) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['technicalMessage'] = $technicalMessage;
        $result['datas'] = $data;
        $result['dataProvider']['totalData'] = $totalData;
        $result['dataProvider']['totalPage'] = $totalPage;
        $result['dataProvider']['currentPage'] = $currentPage;
        $result['dataProvider']['nextPage'] = $nextPage;
        $result['dataProvider']['hasNext'] = $hasNext;


        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
    }

    protected function returnJsonErrorDataNotValid($errorMessage) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = "Data yang dikirim tidak valid";
        $result['technicalMessage'] = $errorMessage;

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
    }

}

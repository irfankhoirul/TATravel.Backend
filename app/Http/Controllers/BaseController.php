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

    const CODE_SUCCESS = 1;
    const CODE_ERROR = 0;

    protected function returnJson($code, $message, $technicalMessage, $data) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['technicalMessage'] = $technicalMessage;
        $result['data'] = $data;

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
        die;
    }

    protected function returnJsonArray($code, $message, $technicalMessage, $datas) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['technicalMessage'] = $technicalMessage;
        $result['datas'] = $datas;

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
        die;
    }

    protected function returnJsonWithPagination($code, $message, $technicalMessage, $data, $hasNext, $totalData, $totalPage, $limit, $currentPage, $nextPage) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['technicalMessage'] = $technicalMessage;
        $result['dataProvider']['totalData'] = $totalData;
        $result['dataProvider']['totalPage'] = $totalPage;
        $result['dataProvider']['currentPage'] = $currentPage;
        $result['dataProvider']['nextPage'] = $nextPage;
        $result['dataProvider']['hasNext'] = $hasNext;
        $result['datas'] = $data;


        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
        die;
    }

    protected function returnJsonErrorDataNotValid($errorMessage) {
        $result = array();
        $result['code'] = self::CODE_ERROR;
        $result['message'] = "Data yang dikirim tidak valid";
        $result['technicalMessage'] = $errorMessage;

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
        die;
    }

    protected function returnJsonErrorNotTokenOwner() {
        $result = array();
        $result['code'] = self::CODE_ERROR;
        $result['message'] = "Anda tidak memiliki akses";

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
        die;
    }

}

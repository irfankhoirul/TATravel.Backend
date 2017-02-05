<?php

namespace TATravel\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected function renderResult($code, $message, $data, $isDataArray) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        if ($isDataArray) {
            $result['datas'] = $data;
        } else {
            $result['data'] = $data;
        }

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
    }

    protected function renderResultWithPagination($code, $message, $data, $hasNext, $totalData, $totalPage, $limit, $currentPage, $nextPage) {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        $result['datas'] = $data;
        $result['dataProvider']['totalData'] = $totalData;
        $result['dataProvider']['totalPage'] = $totalPage;
        $result['dataProvider']['currentPage'] = $currentPage;
        $result['dataProvider']['nextPage'] = $nextPage;
        $result['dataProvider']['hasNext'] = $hasNext;


        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
    }

}

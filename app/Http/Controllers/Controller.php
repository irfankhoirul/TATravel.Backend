<?php

namespace TATravel\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function renderResult($code, $message, $data, $isDataArray)
    {
        $result = array();
        $result['code'] = $code;
        $result['message'] = $message;
        if ($isDataArray) {
            $result['datas'] = $data;
        } else {
            $result['data'] = $data;
        }

        echo(json_encode($result));
    }
}

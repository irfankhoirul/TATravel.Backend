<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;

use TATravel\Http\Requests;
use Illuminate\Support\Facades\DB;

class OperatorTravelController extends BaseController
{
    public function getOperatorTravel(Request $request)
    {
        $operators = DB::table('operator_travel')->get()->toArray();
        if (count($operators) > 0) {
            $newOperators = array();
            foreach ($operators as $operator) {
                if ($operator['id_kota'] != NULL) {
                    $operator['kota'] = DB::table('kota')->where('id', $operator['id_kota'])->first();
                    array_push($newOperators, $operator);
                }
            }
            self::renderResult(1, "Berhasil", $newOperators, TRUE);
        } else {
            self::renderResult(0, "Tidak ada data", null, null);
        }
    }
}

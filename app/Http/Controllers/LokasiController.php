<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;

use Illuminate\Support\Facades\DB;

class LokasiController extends Controller {

    /**
     * Memberikan list lokasi pemberangkatan dan tujuan dari operator travel
     * */
    public function availableLocation() {
        $locations = DB::table('lokasi')->get()->toArray();
        if(count($locations) > 0){
            $newLocations = array();
            foreach ($locations as $location){
                if ($location['id_operator_travel'] != NULL) {
                    $location['operatorTravel'] = DB::table('operator_travel')->where('id', $location['id_operator_travel'])->first();
                    array_push($newLocations, $location);
                }
            }
            self::renderResult(1, "Berhasil", $newLocations, TRUE);
        } else {
            self::renderResult(0, "Tidak Ada Data", NULL, NULL);
        }
    }

}

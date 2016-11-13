<?php

namespace TATravel\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use PDO;
use TATravel\Http\Requests;
use TATravel\JadwalPerjalanan;

class JadwalPerjalananController extends Controller
{

    /**
     * Mengembalikan list jadwal perjalanan travel yg sesuai dengan kriteria pencarian
     *
     * */
    public function availableSchedule(Request $request)
    {
        $date = '2016-12-02 09:00:00';
        $schedules = DB::table('jadwal_perjalanan')->whereDate('waktu_keberangkatan', '=', $date)->get()->toArray();
        if (count($schedules) > 0) {
            $newSchedule = array();
            foreach ($schedules as $schedule) {
                if ($schedule['id_admin'] != NULL) {
                    $schedule['admin'] = DB::table('admin')->where('id', $schedule['id_admin'])->first();
                    array_push($newSchedule, $schedule);
                }
            }

//            $newSchedule = array();
//            foreach ($schedules as $schedule){
//                $admins = array();
//                if($schedule['id_admin'] != NULL){
//                    $scdl = DB::table('admin')->where('id', $schedule['id_admin'])->first();
//                    array_push($admins, $scdl);
//                    array_push($admins, $scdl);
//                    array_push($admins, $scdl);
//                }
//                $schedule['admins'] = $admins;
//                array_push($newSchedule, $schedule);
//            }

            self::renderResult(1, "Berhasil", $newSchedule, TRUE);
        } else {
            self::renderResult(0, "Tidak ada data", null, null);
        }
    }


    /**
     * Mengembalikan list jadwal perjalanan travel A pada hari H
     *
     * */
    public function availableScheduleHour()
    {

    }
}

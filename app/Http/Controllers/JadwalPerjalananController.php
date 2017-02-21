<?php

namespace TATravel\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use TATravel\Http\Requests;
use TATravel\JadwalPerjalanan;
use Validator;

class JadwalPerjalananController extends BaseController {

    /**
     * Post Data :
     * - idDepartureLocation    : Required
     * - idDestinationLocation  : Required
     * - date                   : Required => Long (UTC)
     * - page                   : Required
     */
    public function getList($id, Request $request) {
        $validator = Validator::make($request->all(), [
                    'idDepartureLocation' => 'required|integer|min:1',
                    'idDestinationLocation' => 'required|integer|min:1',
                    'date' => 'required|numeric',
                    'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $idDepartureLocation = $request->request->get('idDepartureLocation');
        $idDestinationLocation = $request->request->get('idDestinationLocation');
        $date = substr($request->request->get('date'), 0, -3);
        $page = $request->request->get('page');

        $schedules = new JadwalPerjalanan();
        list($status, $message, $technicalMessage, $datas, $dataPage) = $schedules->getList($id, $idDepartureLocation, $idDestinationLocation, $date, $page);
        $this->returnJsonWithPagination($status, $message, $technicalMessage, $datas, $dataPage);
    }

    /**
     * Post Data :
     * - idDepartureLocation    : Required
     * - idDestinationLocation  : Required
     * - date                   : Required => Long (UTC)
     */
    public function show($id) {
        $schedule = new JadwalPerjalanan();
        list($status, $message, $technicalMessage, $data) = $schedule->show($id);
        $this->returnJson($status, $message, $technicalMessage, $data);
    }

    /** OLD
     * Mengembalikan list jadwal perjalanan travel yg sesuai dengan kriteria pencarian
     *
     * */
    public function availableSchedule(Request $request) {
        $date = $request->request->get('date');
        $schedules = DB::table('jadwal_perjalanan')->whereDate('waktu_keberangkatan', '=', $date)->get()->toArray();
        if (count($schedules) > 0) {
            $newSchedule = array();
            foreach ($schedules as $schedule) {
                if ($schedule['id_admin'] != NULL) {
                    $schedule['admin'] = DB::table('admin')->where('id', $schedule['id_admin'])->first();
                    array_push($newSchedule, $schedule);
                }
            }
            self::renderResult(1, "Berhasil", $newSchedule, TRUE);
        } else {
            self::renderResult(0, "Tidak ada data", null, null);
        }
    }

    /**
     * Mengembalikan list jadwal perjalanan travel A pada hari H
     *
     * */
    public function availableScheduleHour() {
        
    }

}

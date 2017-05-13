<?php

namespace TATravel\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use TATravel\Http\Requests;
use TATravel\JadwalPerjalanan;
use Validator;
use TATravel\UserTravel;

class JadwalPerjalananController extends BaseController
{

    /**
     * Post Data :
     * - idDepartureLocation    : Required
     * - idDestinationLocation  : Required
     * - date                   : Required => Long (UTC)
     * - page                   : Required
     */
    public function getList($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idDepartureLocations' => 'required',
            'departureLatitude' => 'required',
            'departureLongitude' => 'required',
            'destinationLatitude' => 'required',
            'destinationLongitude' => 'required',
            'idDestinationLocations' => 'required',
            'idPassengers' => 'required',
            'date' => 'required', // ex : 2017-05-20
            'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $idDepartureLocations = $request->request->get('idDepartureLocations');
        $departureLatitude = $request->request->get('departureLatitude');
        $departureLongitude = $request->request->get('departureLongitude');
        $idDestinationLocations = $request->request->get('idDestinationLocations');
        $destinationLatitude = $request->request->get('destinationLatitude');
        $destinationLongitude = $request->request->get('destinationLongitude');
        $idPassengers = $request->request->get('idPassengers');
        $date = $request->request->get('date');
        $page = $request->request->get('page');

        $schedules = new JadwalPerjalanan();
        list($status, $message, $technicalMessage, $datas, $dataPage) =
            $schedules->getList(
                $id,
                $idDepartureLocations,
                $departureLatitude,
                $departureLongitude,
                $idDestinationLocations,
                $destinationLatitude,
                $destinationLongitude,
                $idPassengers,
                $date,
                $page);
        $this->returnJsonWithPagination($status, $message, $technicalMessage, $datas, $dataPage);
    }

    /**
     * Post Data :
     * -
     */
    public function show($id)
    {
        $schedule = new JadwalPerjalanan();
        list($status, $message, $technicalMessage, $data) = $schedule->show($id);
        $this->returnJson($status, $message, $technicalMessage, $data);
    }

    /**
     * Post Data :
     * - page                   : Required
     */
    public function driverScheduleList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $page = $request->request->get('page');

        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        $userId = $data['id'];

        $schedules = new JadwalPerjalanan();
        list($status, $message, $technicalMessage, $datas, $dataPage) = $schedules->getDriverScheduleList($userId, $page);
        $this->returnJsonWithPagination($status, $message, $technicalMessage, $datas, $dataPage);
    }

    /**
     * Post Data :
     * -
     */
    public function showDriverScheduleDetail($id, Request $request)
    {
        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        $schedule = new JadwalPerjalanan();
        if ($schedule->isDriver($id, $data['id'])) {
            list($status, $message, $technicalMessage, $data) = $schedule->show($id);
            $this->returnJson($status, $message, $technicalMessage, $data);
        }
        $this->returnJson(self::CODE_ERROR, "Data tidak ditemukan", NULL, NULL);
    }

    /**
     * Post Data :
     * - Status : Required
     */
    public function setStatus($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|max:1|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        } else {
            $statusSchedule = $request->request->get('status');
            if ($statusSchedule != JadwalPerjalanan::STATUS_ON_THE_WAY &&
                $statusSchedule != JadwalPerjalanan::STATUS_ARRIVED
            ) {
                $this->returnJsonErrorDataNotValid("Data yg dikirim tidak valid");
            }
        }

        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        $schedule = new JadwalPerjalanan();
        if ($schedule->isDriver($id, $data['id'])) {
            list($status, $message, $technicalMessage, $data) = $schedule->setStatus($id, $statusSchedule);
            $this->returnJson($status, $message, $technicalMessage, $data);
        }
        $this->returnJson(self::CODE_ERROR, "Data tidak ditemukan", NULL, NULL);
    }

    /** OLD
     * Mengembalikan list jadwal perjalanan travel yg sesuai dengan kriteria pencarian
     *
     * */
    public function availableSchedule(Request $request)
    {
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

}

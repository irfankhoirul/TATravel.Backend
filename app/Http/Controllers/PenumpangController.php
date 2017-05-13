<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Penumpang;
use TATravel\UserTravel;
use TATravel\Http\Requests;
use Validator;

class PenumpangController extends BaseController {

    /**
     * Post Data :
     * - userId : Required
     * - name   : Required
     */
    public function create($userId, Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|max:128'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $passengerData['id_user'] = $userId;
        $passengerData['nama'] = $request->request->get('name');

        $user = new UserTravel();
        if ($user->isTokenOwner($userId, $request->request->get('token'))) {
            $penumpang = new Penumpang();
            list($status, $message, $technicalMessage, $data) = $penumpang->createPenumpang($passengerData);
            $this->returnJson($status, $message, $technicalMessage, $data);
        }
        $this->returnJsonErrorNoAccess();
    }

    /**
     * Post Data :
     * - id     : Required
     * - userId : Required
     * - name   : Required
     */
    public function update($userId, $id, Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|max:128'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $passengerData['id'] = $id;
        $passengerData['nama'] = $request->request->get('name');

        $user = new UserTravel();
        if ($user->isTokenOwner($userId, $request->request->get('token'))) {
            $penumpang = new Penumpang();
            list($status, $message, $technicalMessage, $data) = $penumpang->getPenumpang($id);
            if ($data['id_user'] == $userId) {
                list($status, $message, $technicalMessage, $data) = $penumpang->updatePenumpang($passengerData);
                $this->returnJson($status, $message, $technicalMessage, $data);
            }
        }
        $this->returnJsonErrorNoAccess();
    }

    public function delete($userId, $id, Request $request) {
        $user = new UserTravel();
        if ($user->isTokenOwner($userId, $request->request->get('token'))) {
            $penumpang = new Penumpang();
            list($status, $message, $technicalMessage, $data) = $penumpang->getPenumpang($id);
            if ($data['id_user'] == $userId) {
                list($status, $message, $technicalMessage) = $penumpang->deletePenumpang($id);
                $this->returnJson($status, $message, $technicalMessage, NULL);
            }
        }
        $this->returnJsonErrorNoAccess();
    }

    /**
     * Post Data :
     * - Page   : Required
     */
    public function getList($userId, Request $request) {
        $validator = Validator::make($request->all(), [
                    'page' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        }

        $page = $request->request->get('page');

        $user = new UserTravel();
        if ($user->isTokenOwner($userId, $request->request->get('token'))) {
            $penumpang = new Penumpang();
            list($status, $message, $technicalMessage, $datas, $dataPage) = $penumpang->listPenumpang($userId, $page);
            $this->returnJsonWithPagination($status, $message, $technicalMessage, $datas, $dataPage);
        }
        $this->returnJsonErrorNoAccess();
    }

    /**
     * Mengembalikan list penumpang yang dibuat oleh user yang bersangkutan
     *
     * */
    public function getPassengerCreatedByMe() {
        
    }

    /**
     * Membuat penumpang baru
     *
     * */
    public function addPassenger() {
        
    }

}

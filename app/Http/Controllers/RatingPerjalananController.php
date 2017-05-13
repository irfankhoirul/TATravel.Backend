<?php

namespace TATravel\Http\Controllers;

use Illuminate\Http\Request;
use TATravel\Http\Requests;
use TATravel\RatingPerjalanan;
use TATravel\Pemesanan;
use TATravel\UserTravel;
use Validator;

class RatingPerjalananController extends BaseController {

    /**
     * Post Data :
     * - IdJadwalPerjalanan : Required
     * - Rating             : Required
     * - Review             : Optional 
     */
    public function rate(Request $request) {
        $validator = Validator::make($request->all(), [
                    'idJadwalPerjalanan' => 'required|integer|min:1',
                    'rating' => 'required|integer|min:1|max:5'
        ]);

        $idJadwalPerjalanann = $request->request->get('idJadwalPerjalanan');
        $rating = $request->request->get('rating');
        $review = $request->request->get('review');

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        } else if ((int) $rating < 1 || (int) $rating > 5) {
            $this->returnJsonErrorDataNotValid("Rating must be between 1 and 5");
        }

        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        // Cek apakah user benar-benar merupakan penumpang perjalanan tersebut
        // Cek apakah sudah pernah memberikan rating, jika sudah -> gagal
        $reservation = new Pemesanan();
        if ($reservation->isBookerUser($data['id'], $idJadwalPerjalanann)) {
            $ratingObject = new RatingPerjalanan();
            $hasRating = $ratingObject->hasRating($data['id'], $idJadwalPerjalanann);
            if (!$hasRating) {
                list($status, $message, $technicalMessage) = $ratingObject->rate($data['id'], $idJadwalPerjalanann, $rating, $review);
                $this->returnJson($status, $message, $technicalMessage, NULL);
            } else {
                $this->returnJson(self::CODE_ERROR, 'Anda sudah memberikan rating', NULL, NULL);
            }
        }
        $this->returnJsonErrorNoAccess();
    }

    public function update($id, Request $request) {
        $validator = Validator::make($request->all(), [
                    'idJadwalPerjalanan' => 'required|integer|min:1',
                    'rating' => 'required|integer|min:1|max:5'
        ]);

        $idJadwalPerjalanann = $request->request->get('idJadwalPerjalanan');
        $rating = $request->request->get('rating');
        $review = $request->request->get('review');

        if ($validator->fails()) {
            $this->returnJsonErrorDataNotValid($validator->errors());
        } else if ((int) $rating < 1 || (int) $rating > 5) {
            $this->returnJsonErrorDataNotValid("Rating must be between 1 and 5");
        }

        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        // Cek apakah user benar-benar merupakan penumpang perjalanan tersebut
        $reservation = new Pemesanan();
        if ($reservation->isBookerUser($data['id'], $idJadwalPerjalanann)) {
            $ratingObject = new RatingPerjalanan();
            list($status, $message, $technicalMessage) = $ratingObject->updateRating($id, $rating, $review);
            $this->returnJson($status, $message, $technicalMessage, NULL);
        }
        $this->returnJsonErrorNoAccess();
    }

    public function delete($id, Request $request) {
        $user = new UserTravel();
        list($status, $message, $technicalMessage, $data) = $user->getUserByToken($request->request->get('token'));

        // Cek apakah user benar-benar merupakan penumpang perjalanan tersebut
        $ratingObject = new RatingPerjalanan();
        list($status, $message, $technicalMessage, $ratingData) = $ratingObject->show($id);
        $reservation = new Pemesanan();
        if ($reservation->isBookerUser($data['id'], $ratingData['id_jadwal_perjalanan'])) {
            list($status, $message, $technicalMessage) = $ratingObject->deleteRating($id);
            $this->returnJson($status, $message, $technicalMessage, NULL);
        }
        $this->returnJsonErrorNoAccess();
    }

}

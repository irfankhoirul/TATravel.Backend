<?php
/**
 * Created by PhpStorm.
 * User: AsusPC
 * Date: 5/6/2017
 * Time: 7:08 AM
 */

namespace TATravel\Util;


class DistanceUtil
{

    public function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371.0; // kilo meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lon2 - $lon1);
        $sindLat = sin($dLat / 2);
        $sindLng = sin($dLng / 2);
        $a = pow($sindLat, 2) + pow($sindLng, 2)
            * cos(deg2rad($lat1)) * cos(deg2rad($lat2));
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $dist = $earthRadius * $c;

        return $dist;
    }

}
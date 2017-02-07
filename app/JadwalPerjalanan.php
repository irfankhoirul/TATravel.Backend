<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;

class JadwalPerjalanan extends BaseModel
{
    const STATUS_SCHEDULED = 'S';
    const STATUS_ON_THE_WAY = 'O';
    const STATUS_ARRIVED = 'A';
    const STATUS_CANCELLED = 'C';
    const STATUS_DELAYED = 'D';

    protected $table = 'jadwal_perjalanan';

}

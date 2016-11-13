<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;

class KursiPerjalanan extends Model
{
    const STATUS_AVAILABLE = 'A';
    const STATUS_UNAVAILABLE = 'U';

    protected $table = 'kursi_perjalanan';
}

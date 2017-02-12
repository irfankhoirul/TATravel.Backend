<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model {
    
    const CODE_SUCCESS = 0;
    const CODE_ERROR = 1;
    
    const QUERY_SUCCESS = 1;
    
    const STATUS_ACTIVE = 'A';
    const STATUS_VOID = 'V';
    const STATUS_INACTIVE = 'I';
    const STATUS_EXPIRED = 'E';

}

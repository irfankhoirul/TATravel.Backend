<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model{
    protected $table = 'user_device';

    public function registerDevice($device){
      DB::table('users')->insert(['email' => 'john@example.com', 'votes' => 0]);
    }
}

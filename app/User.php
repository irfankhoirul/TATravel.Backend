<?php

namespace TATravel;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    const USER_TYPE_USER = 'U';
    const USER_TYPE_ADMIN = 'A';
    const USER_TYPE_SUPER_ADMIN = 'S';
    const USER_TYPE_DRIVER = 'D';

    const USER_REGISTRATION_STEP_REGISTERED = 'D';
    const USER_REGISTRATION_STEP_VERIFIED = 'V';

    protected $table = 'user';

    public function register($userData){
      $user = DB::table('user')->where('email', $request->request->get('email'))->first();
      $toBeHashed = $request->request->get('password') . $user['salt'];
    }
}

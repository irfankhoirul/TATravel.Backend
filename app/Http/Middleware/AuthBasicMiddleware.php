<?php

namespace TATravel\Http\Middleware;

use Closure;
use TATravel\UserToken;
use Validator;

class AuthBasicMiddleware extends BaseAuthMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $validator = Validator::make($request->all(), [
                    'token' => 'required',
        ]);

        // Cek token dikirim
        if ($validator->fails()) {
            return $this->returnJsonErrorAuthentication('Token tidak dikirim');
        }
               
        // Cek token aktif
        $token = $request->request->get('token');       
        $userToken = new UserToken();
        list($status, $message, $technicalMessage) = $userToken->checkToken($token);
        if($status === self::CODE_ERROR) {
            return $this->returnJsonErrorAuthentication($message);
        }

        $userToken->incrementRequestCount($token);

        return $next($request);
    }

}

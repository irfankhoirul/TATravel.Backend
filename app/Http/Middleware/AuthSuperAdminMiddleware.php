<?php

namespace TATravel\Http\Middleware;

use Closure;
use TATravel\UserToken;
use TATravel\UserTravel;
use TATravel\Http\Middleware\BaseAuthMiddleware;

class AuthSuperAdminMiddleware extends BaseAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Cek tipe user berdasarkan token
        $token = $request->request->get('token');
        $userToken = new UserToken();
        list($status, $message, $technicalMessage, $data) = $userToken->getToken($token);
        if ($status == self::CODE_ERROR) {
            return $this->returnJsonErrorAuthentication($message);
        }

        $user = new UserTravel();
        $userData = $user->getUser($data['id_user']);
        if ($userData['tipe'] != self::USER_TYPE_SUPER_ADMIN) {
            return $this->returnJsonErrorAuthentication(self::AUTHENTICATION_FAILED);
        }

        $userToken->incrementRequestCount($token);

        return $next($request);
    }
}

<?php

namespace TATravel\Http\Middleware;

use Closure;
use TATravel\Http\Middleware\AuthMiddleware;

class BaseAuthMiddleware {

    const CODE_SUCCESS = 1;
    const CODE_ERROR = 0;
    const USER_TYPE_USER = 'U';
    const USER_TYPE_ADMIN = 'A';
    const USER_TYPE_SUPER_ADMIN = 'S';
    const USER_TYPE_DRIVER = 'D';
    const AUTHENTICATION_FAILED = "Authentication failed";

    protected function returnJsonErrorAuthentication($errorMessage) {
        $result = array();
        $result['code'] = self::CODE_ERROR;
        $result['message'] = $errorMessage;

        header('Content-Type: application/json');
        echo(json_encode($result, JSON_PRETTY_PRINT));
        die;
    }

}

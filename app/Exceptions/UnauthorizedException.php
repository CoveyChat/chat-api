<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedException extends HttpException
{
    protected $message = "Unauthorized";
    protected $httpCode = 401;
}

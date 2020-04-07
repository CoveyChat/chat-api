<?php

namespace App\Exceptions;

use Exception;

class InvalidPermissionsException extends HttpException
{
    protected $message = "Unauthorized";
    protected $httpCode = 401;
}

<?php

namespace App\Exceptions;

use Exception;

class MethodNotAllowedException extends HttpException
{
    protected $message = "Method Not Allowed";
    protected $httpCode = 405;
}

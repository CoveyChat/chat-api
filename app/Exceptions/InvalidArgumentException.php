<?php

namespace App\Exceptions;

use Exception;

class InvalidArgumentException extends HttpException
{
    protected $message = "Invalid arguments provided";
    protected $httpCode = 400;
}

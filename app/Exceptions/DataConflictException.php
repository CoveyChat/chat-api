<?php

namespace App\Exceptions;

use Exception;

class DataConflictException extends HttpException
{
    protected $message = "Already exists";
    protected $httpCode = 409;
}

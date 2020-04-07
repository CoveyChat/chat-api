<?php

namespace App\Exceptions;

class NotImplementedException extends HttpException
{
    protected $message = "This request route is not implemented";
    protected $httpCode = 403;
}


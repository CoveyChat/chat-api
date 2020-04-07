<?php

namespace App\Exceptions;

class NotFoundException extends HttpException
{
    protected $message = "No data found";
    protected $httpCode = 404;
}


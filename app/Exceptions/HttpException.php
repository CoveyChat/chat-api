<?php

namespace App\Exceptions;

use Exception;


class HttpException extends Exception
{
    protected $data = [];
    protected $message = "Server Error";
    protected $httpCode = 500;

    public function __construct($message = null, $data=null) {
        $this->message = $message ?? $this->message;
        $this->data = $data;
    }

    public function render() {
        return response()->api()->error($this->data, $this->httpCode, $this->message);
    }
}


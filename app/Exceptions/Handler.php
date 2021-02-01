<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;


use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        //Catch any bad ids in route
        if ($exception instanceof ModelNotFoundException) {
            //Get just the model name and not the whole namespace
            $modelName = array_slice(explode("\\", $exception->getModel()), -1, 1)[0];

            throw new NotFoundException("could not find '" . $modelName . "' id " . implode(",", $exception->getIds()));
        }

        //Catch any malformed / non-existent routes
        if ($exception instanceof NotFoundHttpException) {
            throw new NotFoundException('invalid or malformed route');
        }

        //Catch any malformed / non-existent routes
        if ($exception instanceof MethodNotAllowedHttpException) {
            throw new MethodNotAllowedException();
        }

        //Catch any malformed request data
        if ($exception instanceof ValidationException) {
            throw new InvalidArgumentException('validation error', $exception->validator->errors());
        }

        return parent::render($request, $exception);
    }
}

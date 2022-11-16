<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Throwable $exception, $request) {

            if ($exception instanceof ValidationException) {
                return $this->errorResponse($exception->validator->errors()->getMessages(), 422);
            }

            if ($exception instanceof ModelNotFoundException) {
                $modelo = strtolower(class_basename($exception->getModel()));
                return $this->errorResponse("No existe ninguna instancia de {$modelo} con el id especificado", 404);
            }

            if ($exception instanceof AuthenticationException) {
                return $this->errorResponse('No autenticado.', 401);
                //return $this->unauthenticated($request, $exception);
            }

            /*if ($exception instanceof ClientException) {
                return $this->errorResponse('credenciales invalidas', 401);
            }*/

            if ($exception instanceof AuthorizationException) {
                return $this->errorResponse('No posee permisos para ejecutar esta acción', 403);
            }

            if ($exception instanceof NotFoundHttpException) {
                return $this->errorResponse('No se encontró la URL especificada', 404);
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return $this->errorResponse('El método especificado en la petición no es válido', 405);
            }

            if ($exception instanceof HttpException) {
                return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
            }

            if ($exception instanceof QueryException) {
                $codigo = $exception->errorInfo[1];

                if ($codigo == 1451) {
                    return $this->errorResponse('No se puede eliminar de forma permamente el recurso porque está relacionado con algún otro.', 409);
                }
            }

            if ($exception instanceof TokenMismatchException) {
                return redirect()->back()->withInput($request->input());
            }

            dd($exception);
            return $this->errorResponse($exception->getMessage(), 500);
            return $this->errorResponse('Falla inesperada. Intente luego', 500);
        });

       

    }
}

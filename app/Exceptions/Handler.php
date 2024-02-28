<?php

namespace App\Exceptions;

use App\Http\Resources\Default\ApiResponseResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];
    protected $dontReport = [];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            return (new ApiResponseResource(
                message: $e->getMessage(),
                type: false
            ))
                ->response()
                ->setStatusCode($e->getCode() > 100 ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }

    public function render($request, Throwable $e
    ): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|\Illuminate\Http\RedirectResponse {
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors'  => $e->errors(),
            ], 422);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Dado não encontrado!',
                'type'    => false
            ], 404);
        }
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => "Não autorizado!",
                'type'    => false,
            ], 401);
        }

        if ($e instanceof HttpExceptionInterface) {
            return response()->json([
                'message' => $e->getMessage(),
                'type'    => false,
            ], $e->getStatusCode() > 100 ? $e->getStatusCode() : 500);
        }

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        return response()->json([
            'message' => $e->getMessage(),
            'type'    => false,
        ], ($e->getCode() > 100 ? $e->getCode() : 500));
    }
}

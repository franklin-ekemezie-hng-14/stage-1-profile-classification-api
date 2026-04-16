<?php

use App\Exceptions\ExternalApiException;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '/api'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //

        $exceptions->render(function (NotFoundHttpException $e) {


            $previousException = $e->getPrevious();

            if ($previousException instanceof ModelNotFoundException) {

                $message = match ($previousException->getModel()) {
                    Profile::class  => 'Profile not found.',
                    default         => null
                };

                if ($message) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => $message,
                    ], Response::HTTP_NOT_FOUND);
                }

            }


            return null;
        });



        $exceptions->render(function (ExternalApiException $e) {
            return response()->json($e->toResponse(), Response::HTTP_BAD_GATEWAY);
        });

    })->create();

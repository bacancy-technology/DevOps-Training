<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable,Exception,Auth,Log;

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

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*') || $request->wantsJson()) { // This is an API request
            $exception_msg = $exception->getMessage();
            $return_data = \App\Http\Controllers\API\ApiCommonController::apiCommonResponseData();
            $return_data['message'] = $exception_msg;
            $server_error_code = 500;

            if ($exception_msg == 'Unauthenticated.') {
                $server_error_code = 401;
            }

            // error log
            Log::channel('appLogs')->error($exception);

            return response()->json($return_data, $server_error_code);
        } else { // This is a web request
            return parent::render($request, $exception);
        }
    }
}

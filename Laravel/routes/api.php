<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthenticateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/********** non authenticate routes ************/
Route::controller(AuthenticateController::class)->group(function(){
    Route::post('initial-app', 'initialApp');
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('2factor-verification', 'twoFactorVerification');
    Route::post('2factor-resend', 'twoFactorResend');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
    Route::post('logout', 'logout');
});
/********** /non authenticate routes ************/

/********** authenticate routes ************/
Route::group(['middleware' => ['auth:api']], function () {
    Route::controller(AuthenticateController::class)->group(function(){
        Route::post('getuser', 'getUser');
    });
});
/********** /authenticate routes ************/


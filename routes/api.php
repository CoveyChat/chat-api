<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api', 'prefix' => '1.0'], function () use ($router) {

    Route::group(['prefix' => 'chats'], function() {
        Route::get('{chat}', 'ChatController@get');
        Route::patch('{chat}', 'ChatController@update');
    });

    Route::post('users/login', 'AuthController@login');

    Route::group(['middleware' => 'auth:api'], function() {
        Route::group(['prefix' => 'users'], function() {
            Route::get('whoami', 'UserController@whoami');
        });
    });

});
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

    Route::post('users/login', 'AuthController@login');

    Route::post('users/register', 'UserController@register');
    Route::post('users/password/reset', 'UserController@passwordReset')->name('password.reset');
    Route::post('users/password/forgot', 'UserController@passwordForgot');

    Route::group(['prefix' => 'chats'], function() {
        Route::get('{chat}', 'Chat\ChatController@get');
        Route::post('', 'Chat\ChatController@create');
        Route::patch('{chat}', 'Chat\ChatController@update');
        Route::delete('{chat}', 'Chat\ChatController@delete');
    });

    //Authenticated Routes
    Route::group(['middleware' => 'auth:api'], function() {
        Route::post('users/logout', 'AuthController@logout');

        Route::group(['prefix' => 'users'], function() {
            Route::get('whoami', 'UserController@whoami');
            Route::patch('users/password', 'AuthController@updatePassword');

            Route::get('{user_id}/chats/', 'Chat\ChatController@getUserChat');
            Route::post('{user_id}/chats', 'Chat\ChatController@createUserChat');
            Route::put('{user_id}/chats/{chat}', 'Chat\ChatController@update');
            Route::delete('{user_id}/chats/{chat}', 'Chat\ChatController@delete');
        });
    });

});
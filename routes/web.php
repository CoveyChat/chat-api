<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {return view('home');});

Route::middleware('auth:web')->get('/bridge/usertoken', 'Api\AuthController@get');

Route::get('oauth/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('oauth/{provider}/callback', 'Auth\LoginController@handleProviderCallback');


Route::get('/profile', 'ProfileController@index')->name('profile');
Route::get('/terms', function () {return view('terms');});
Route::get('/privacy', function () {return view('privacy');});

Route::get('/chat', 'Chat\ChatController@index');
Route::post('/chat', 'Chat\ChatController@create')->name('chat_create');
Route::get('/chat/{chat}', 'Chat\ChatController@launch')->name('chat_launch');
Route::post('/chat/{chat}', 'Chat\ChatController@verifyLaunchPassword');


Route::group(['prefix' => 'bridge'], function() {
    Route::get('whoami', 'Api\UserController@whoami');
});
*/

//Auth::routes();

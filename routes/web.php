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

Route::get('/', function () {
    return view('welcome');
});


Route::get('oauth/{provider}', 'Auth\LoginController@redirectToProvider');
Route::get('oauth/{provider}/callback', 'Auth\LoginController@handleProviderCallback');


Route::get('/home', 'HomeController@index')->name('home');
Route::get('/terms', 'HomeController@index')->name('home');
Route::get('/privacy', 'HomeController@index')->name('home');

Route::get('/chat', 'Chat\ChatController@index');
Route::post('/chat', 'Chat\ChatController@create')->name('chat_create');
Route::get('/chat/{chat}', 'Chat\ChatController@launch')->name('chat_launch');


Route::group(['prefix' => 'myuser'], function() {
    Route::get('whoami', 'Api\UserController@whoami');
});


Auth::routes();

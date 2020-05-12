<?php

use Illuminate\Support\Facades\Auth;
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

Route::get('/getfriends', 'HomeController@getfriends');
Route::post('/session/create', 'SessionController@create');
Route::get('/session/chats/{session_id}', 'ChatController@chats');
Route::get('/session/unread/{session_id}', 'ChatController@read');
Route::get('/session/clear/{session_id}', 'ChatController@clear');
Route::get('/session/block/{session_id}', 'BlockController@block');
Route::get('/session/unblock/{session_id}', 'BlockController@unblock');
Route::post('/send/{session}', 'ChatController@send');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

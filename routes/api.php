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


Route::post('/send/signup-mail' , 'UsersController@sendSignUpMail');
Route::post('/base-register' , 'UsersController@BaseRegister');
Route::post('/final-register' , 'UsersController@FinalRegister');

Route::post('/login','UsersController@login');

Route::middleware('auth:api')->group(function ()
{
    Route::delete('/logout','UsersController@logout');
    Route::post('/update/{id}','UsersController@Update');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

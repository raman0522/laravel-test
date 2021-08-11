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

Route::get('/login' , function(){
    $info['status_code'] = '0';
    $info['status_text'] = 'failed';
    $info['message'] = 'UnAuthenticated';
    return response($info,401);
})->name('login');

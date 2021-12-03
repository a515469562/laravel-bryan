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

Route::namespace('App\Http\Controllers\Api')
        ->middleware('auth:api')
        ->group(function (){
            Route::get('redis','CommonController@testRedis');
            Route::get('mysql','CommonController@testMysql');
        });
Route::namespace('App\Http\Controllers\Api')
        ->group(function(){
            Route::post('login','CommonController@login');
        });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

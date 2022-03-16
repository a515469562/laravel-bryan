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
            Route::post('mysql','CommonController@testMysql');
            Route::post('pessimistic_lock','CommonController@psLock');//悲观锁， 文件锁/redis
            Route::post('optimistic_lock','CommonController@opLock');//乐观锁，版本控制
            Route::post('repeat_click', 'CommonController@repeatClick');
            Route::post('redis_lock', 'CommonController@redisLock');
//            Route::any('file_export', 'CommonController@bigFileExport');
            Route::post('queue', 'CommonController@queueTest');




            //死锁测试
            Route::post('deadlock','CommonController@deadlockTest');
            Route::post('deadlock2','CommonController@deadlockTest2');
            //RabbitMq测试
            Route::post('producer','CommonController@producer');

            //图片处理
            Route::post('image_merge','CommonController@imageMerge');

        });
Route::namespace('App\Http\Controllers\Api')
        ->group(function(){
            Route::any('file_export', 'CommonController@bigFileExport');
            Route::post('register','CommonController@register');
            Route::post('login','CommonController@login');
            Route::get('single','CommonController@singleTest');
        });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Service\CommonService;
use Illuminate\Support\Facades\Redis;

class CommonController extends Controller
{

    public function testRedis(){
        try {
            Redis::setex('bryan', 60, 'bryan is a name');
            $res = Redis::get('bryan');
            return self::apiSuccess($res) ;
        }catch (\Exception $e){
            return self::apiError('redis异常：' . $e->getMessage());
        }

    }

    public function testMysql(){
        try {
            $user = User::query()->get()->toArray();
            return self::apiSuccess($user);
        }catch (\Exception $e){
            return self::apiError('mysql异常:' . $e->getMessage());
        }

    }

}

<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class CommonController extends Controller
{
    const GUARD = 'api';

    public function login(){
        $guard = self::GUARD;
        $credentials = request()->only(['email', 'password']);
        $paramsCount = 2;
        if (empty($credentials) || count($credentials) < $paramsCount){
            return self::apiError('参数错误');
        }
        $token = Auth::guard($guard)->attempt($credentials);
        if ($token){
            $user = Auth::guard($guard)->user()->toArray();
            $expire = date('Y-m-d H:i:s',time() +  env('JWT_TTL') * 60);
            $data = [
                'user_info' => $user,
                'token' => env('JWT_PREFIX', 'Bearer ') . $token,
                'expire_in' => $expire
            ];

            return self::apiSuccess($data);
        }else{
            return self::apiError('登录失败');
        }

    }

    public function testRedis(){
        $guard = self::GUARD;
        try {
            $user = Auth::guard($guard)->user();
            Redis::setex('bryan', 60, "{$user['name']}'s email is {$user['email']}");
            $res = Redis::get('bryan');
            return self::apiSuccess($res) ;
        }catch (\Exception $e){
            return self::apiError('redis异常：' . $e->getMessage());
        }

    }

    public function testMysql(){
        $guard = self::GUARD;
        try {
            $user = Auth::guard($guard)->user();
            return self::apiSuccess($user);
        }catch (\Exception $e){
            return self::apiError('mysql异常:' . $e->getMessage());
        }

    }

}

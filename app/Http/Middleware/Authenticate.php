<?php

namespace App\Http\Middleware;

use Closure;
use Dotenv\Validator;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class Authenticate extends Middleware
{

    public function handle($request, Closure $next, $guard = 'api')
    {
        $token = $request->header('Authorization');
        $parseToken = Auth::guard($guard);
        if (empty($token) ){
            return apiError('请重新登录');
        }
        try {
            $check = $parseToken->parseToken()->check();//1直接通过密钥和算法解密校验，之后使用auth::user需要db获取；
        }catch (\Exception $e){
//            var_dump($e->getMessage());
            return apiError("请重新登录");
        }
//        $check = Auth::guard($guard)->check();//2校验会与数据库交互获取信息，之后使用auth::user()时可直接使用不用db获取
        if (!$check){
            return apiError('请重新登录');
        }else {
            $response = $next($request);
            try {
                $expireTime = Auth::guard($guard)->payload()['exp'];
                //token过期前gapTime(秒)若有请求，则刷新token
                $gapTime = 10*60;
                if ($expireTime - time() > 0 && $expireTime - time() < $gapTime) {
                    $refresh = Auth::guard($guard)->refresh();//获取新token重新计时，旧token自动失效
                    //更新请求头
                    $request->headers->set('Authorization', 'Bearer '.$refresh);
                    //更新返回头
                    $response->headers->set('Authorization', 'Bearer '.$refresh);
                    return $response;
                }
            }catch (\Exception $e){
                return apiError('发生异常:'.$e->getMessage());
            }

            return $response;
        }

    }


}

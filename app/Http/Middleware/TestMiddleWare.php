<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use phpseclib3\File\ASN1\Maps\PrivateDomainName;

class TestMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //前置中间件
//        var_dump("前置中间件");
        $response = $next($request);//传入程序中并返回
//        后置中间件
//        var_dump("后置中间件");
//        var_dump("this is a test middleware");
//        var_dump("get_request:");
//        var_dump($request);

        return $response;
    }
}

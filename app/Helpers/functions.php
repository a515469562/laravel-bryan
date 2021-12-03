<?php

if(!function_exists('apiError')){
    /**
     * 返回异常
     * @param string $message
     * @param int $code
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    function apiError(string $message, int $code = 500){
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => []
        ];
        return response($response);
    }
}

if (!function_exists('apiSuccess')){
    /**
     * 返回成功相应
     * @param $data
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
     function apiSuccess($data){
        $response = [
            'code' => 200,
            'message' => 'ok',
            'data' => $data
        ];
        return response($response);
    }
}




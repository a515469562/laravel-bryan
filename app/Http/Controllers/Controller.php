<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected static function apiSuccess($result){
        $result = [
            'code' => 200,
            'message' => 'ok',
            'data' => $result
        ];
//        return json_encode($result, JSON_UNESCAPED_UNICODE);
        return response($result);
    }

    protected static function apiError($errMessage, $errCode = 500){
        $result = [
            'code' => $errCode,
            'message' => $errMessage,
            'data' => []
        ];
        return json_encode($result, JSON_UNESCAPED_UNICODE);
//        return response($result, $errCode);
    }

}

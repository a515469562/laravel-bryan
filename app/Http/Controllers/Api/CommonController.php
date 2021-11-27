<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;

class CommonController extends Controller
{

    public function test(){

        $result = [
            'code' => 200,
            'message' => 'ok',
            'data' => []
        ];
//        return json_encode($result);
        return response($result);
    }

}

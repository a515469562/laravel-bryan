<?php

use Illuminate\Support\Facades\DB;


if (!function_exists('apiError')) {
    /**
     * 返回异常
     * @param string $message
     * @param int $code
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    function apiError(string $message, int $code = 500)
    {
        $response = [
            'code'    => $code,
            'message' => $message,
            'data'    => []
        ];
        return response($response, $code);
    }
}

if (!function_exists('apiSuccess')) {
    /**
     * 返回成功相应
     * @param $data
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    function apiSuccess($data)
    {
        $response = [
            'code'    => 200,
            'message' => 'ok',
            'data'    => $data
        ];
        return response($response);
    }
}


if (!function_exists('infiniteTree')) {
    function infiniteTree()
    {
        $list = DB::table('auths')->select(['id', 'pid', 'auth_name'])->get()->toArray();
        //1.递归法
//        $result = getTree($list);
        //2.引用法
        $tree = [];
        $list = array_column($list, null, 'id');
        foreach ($list as $key => $value) {
            if (!is_array($list[$key])) {
                $value = (array)$value;
                $list[$key] = (array)$list[$key];
            }
            if (isset($list[$value['pid']])) {
                //子节点
                $list[$value['pid']]['children'][] = &$list[$key];
            } else {
                //根节点
                $tree[] = &$list[$key];
            }
        }
        $result = $tree;
        return $result;
    }
}

//1.获取递归树
if (!function_exists('getTree')) {
    function getTree(array $list, int $pid = 0)
    {
        $tree = [];
        foreach ($list as $item) {
            if (!is_array($item)) {
                $item = (array)$item;
            }
            if ($item['pid'] == $pid) {
                $item['children'] = getTree($list, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;

    }
}


//创建密钥对
if (!function_exists('publicPrivateKey')) {
    function publicPrivateKey($path = '')
    {
        if (empty($path)) {
            $path = __DIR__ . '/openssl.cnf';
        }
//创建密钥对
        $config = [
            "digest_alg"       => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            'config'           => $path
        ];

        $res = openssl_pkey_new($config);
        $privkey = '';
//生成私钥
        openssl_pkey_export($res, $privkey, null, $config);
//生成公钥
        $pubKey = openssl_pkey_get_details($res)['key'];

        print_r($privkey);
        print_r($pubKey);

        file_put_contents($path, $pubKey, FILE_APPEND);
        file_put_contents($path, $privkey, FILE_APPEND);
    }
}

if (!function_exists('getMicroTime')) {

    function getMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return (int)(((float)$usec + (float)$sec) * 1000);

    }
}


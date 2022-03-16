<?php
//0.0.0.0 表示监听所有 IP 地址，一台服务器可能同时有多个 IP，如 127.0.0.1 本地回环 IP、192.168.1.100 局域网 IP、210.127.20.2 外网 IP，这里也可以单独指定监听一个 IP
$http = new Swoole\Http\Server('0.0.0.0', 9503);

$http->on('Request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});

$http->start();

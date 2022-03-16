<?php

//创建WebSocket Server对象，监听0.0.0.0:9502端口
//websocket支持长连接的协议，且可以双向传输
// http是短连接，新版本是持久连接仍然是短连接， 且只能单向传输 request=>response
$ws = new Swoole\WebSocket\Server('0.0.0.0', 9504);

//监听WebSocket连接打开事件
$ws->on('Open', function ($ws, $request) {
    $ws->push($request->fd, "hello, welcome\n");
});

//监听WebSocket消息事件
$ws->on('Message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
    $ws->push($frame->fd, "server: {$frame->data}");
});

//监听WebSocket连接关闭事件
$ws->on('Close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();

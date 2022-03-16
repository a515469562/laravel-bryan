<?php
require_once '../vendor/autoload.php';


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'root');
$channel = $connection->channel();
//0.声明队列
$channel->queue_declare('rpc_queue', false, false, false, false);
function fib($n)
{
    if ($n == 0) {
        return 0;
    }
    if ($n == 1) {
        return 1;
    }
    return fib($n-1) + fib($n-2);
}

echo " [x] Awaiting RPC requests\n";
$callback = function ($req) {
    //1.1监听rpc队列，处理client发送的消息
    $n = intval($req->body);
    echo ' [.] fib(', $n, ")\n";

    //1.2.返回处理结果，并携带请求标识
    $msg = new AMQPMessage(
        (string) fib($n),
        array('correlation_id' => $req->get('correlation_id'))
    );
    //2.发送消息至同一信道的 回调队列， 由client监听消费。
    $req->delivery_info['channel']->basic_publish(
        $msg,
        '',
        $req->get('reply_to')
    );
    //3.消息接受确认
    $req->ack();
};

//设置预加载数量，服务端worker公平调度
$channel->basic_qos(null, 1, null);
//轮训消费，监听rpc队列
$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();


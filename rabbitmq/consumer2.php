<?php

require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'root');
$channel = $connection->channel();

//$severity = array('info', 'error');
//$bindingKeys = $severity;

//$channel->exchange_declare('fanout_logs', 'fanout', false, true, false);//1.fanout模式
//$channel->exchange_declare('direct_logs', 'direct', false, true, false);//2.direct模式
$channel->exchange_declare('topic_logs', 'topic', false, true, false);//3.topic模式
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$binding_keys = array_slice($argv, 1);
if (empty($binding_keys)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
    exit(1);
}

//$channel->queue_bind($queue_name, 'logs');//1.队列绑定交换机，fanout模式

//一个队列多个binding_key；
foreach ($binding_keys as $bindingKey) {
    $channel->queue_bind($queue_name, 'topic_logs', $bindingKey);//2.1.队列绑定交换机，订阅模式且非fanout模式，binding_key
}
//$channel->queue_bind($queue_name, 'fanout_logs');//2.队列绑定交换机
//$bindingKey = '#';//相当于全部消息都能接收
//$bindingKey = 'black.#';//相当于全部消息都能接收
//$bindingKey = 'black.*.*';//相当于全部消息都能接收
//$bindingKey = 'black.tall.*';//black,tall,下的所有大小都可以接收
//$bindingKey = 'black.tall.small';//无法接收
//$bindingKey = 'black.tall.big';//相当于全部消息都能接收
//$channel->queue_bind($queue_name, 'topic_logs', $bindingKey);//2.队列绑定交换机,声明binding_key
$binding_key_last = array_pop($binding_keys);



echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function ($msg) use($binding_key_last){
    sleep(1);
    echo 'msg_routing_key is ' . $msg->delivery_info['routing_key'] . PHP_EOL;
    echo 'msg_binding_key is ' . $binding_key_last . PHP_EOL;
    echo ' [x] ', $msg->body, "\n";
    $msg->nack();
//    $msg->nack();


};

//一个队列绑定一个binding_key，创建2个队列
//foreach ($severity as $item) {
//    list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
//    $bindingKey = $item;
//    $channel->queue_bind($queue_name, 'direct_logs', $bindingKey);
//    $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
//}

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

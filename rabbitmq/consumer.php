<?php
require_once "../vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;


$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'root');
$channel = $connection->channel();
$channel->queue_declare('task_queue', false, true, false, false);
echo " [*] Waiting for messages. To exit press CTRL+C\n";
$callback = function ($msg) {
    echo "consumer received : " . $msg->body . PHP_EOL;
//    sleep(1);
    echo "Done" . PHP_EOL;
    //确认消息
    $msg->ack();
};
//公平调度, 设置预加载个数
$channel->basic_qos(null, 1, null);
//持续监听，回调处理消息
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);
while ($channel->is_open()) {
    $channel->wait();
}


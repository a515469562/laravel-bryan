<?php
require_once "../vendor/autoload.php";

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//1.建立连接
$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'root');
//2.信道
$channel = $connection->channel();
//3.信道中声明队列
$channel->queue_declare("task_queue", false, true, false, false);
$message = "Hello Task";
//4.生成amqp消息
$msg = new AMQPMessage($message, [
    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
]);//投递模式设置为消息持久化
//5.发布消息
$channel->basic_publish($msg, '', 'task_queue');
echo "publisher  Sent '{$message}!'\n";
$channel->close();
$connection->close();





<?php

require_once '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'root', 'root');
$channel = $connection->channel();
$channel->confirm_select();

//$channel->exchange_declare('fanout_logs', 'fanout', false, true, false);//1.fanout路由
//$channel->exchange_declare('direct_logs', 'direct', false, true, false);//2.1.direct路由
$channel->exchange_declare('topic_logs', 'topic', false, true, false); //3.1.topics路由


$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
$data = implode(' ', array_slice($argv, 2));
if (empty($data)) {
    $data = "Hello World!";
}

$msg = new AMQPMessage($data);

//$data = implode(' ', array_slice($argv, 1));
//$severityInfo = 'info';
//$severityError = 'error';
//if (empty($data)) {
//    $rawMsgInfo = "{$severityInfo}: Hello World!";
//    $rawMsgError = "{$severityError}: Hello World!";
//    $msgInfo = new AMQPMessage($rawMsgInfo);
//    $msgError = new AMQPMessage($rawMsgError);
//}
//$channel->basic_publish($msg, 'fanout_logs');//1.fanout模式
//$channel->basic_publish($msg, 'direct_logs', $routing_key);//2.发布消息至交换机，携带routing_key
$routing_key = 'black.tall.big';
$channel->basic_publish($msg, 'topic_logs', $routing_key);//2.发布消息至交换机，携带routing_key
//$channel->basic_publish($msg, 'topic_logs', $routing_key);//3.topics
//7.1. sync-mode
//try {
//$channel->wait_for_pending_acks(1);
//}catch (Exception $exception){
//    echo "exception:" . $exception->getMessage() . PHP_EOL;
//}

//7.2. async-mode
$channel->set_ack_handler(function (AMQPMessage $msg) {
    echo "ack msg" . PHP_EOL;
    file_put_contents('./ackfile.txt', '[' . json_encode(microtime() ) . ']' . json_encode($msg), FILE_APPEND);
});

$channel->set_nack_handler(function (AMQPMessage $msg) {
    echo "nack msg" . PHP_EOL;
    file_put_contents('./nackfile.txt', '[' . time() . ']' . json_encode($msg), FILE_APPEND);
});

$channel->wait_for_pending_acks(10);

echo ' [x] Sent '.json_encode(microtime() ), $routing_key, ':', $data, "\n";

//$channel->basic_publish($msgInfo, 'direct_logs', $severityInfo);//2.2消息assign指定routing_key;
//echo "[{$severityInfo}] Sent " . $rawMsgInfo . "\n";
//$channel->basic_publish($msgError, 'direct_logs', $severityError);
//echo "[{$severityError}] Sent " . $rawMsgError . "\n";
//$severityMiss = 'non-routing';
//$channel->basic_publish($msgInfo, 'direct_logs', $severityMiss);
//echo "[{$severityMiss}] Sent " . $severityMiss . "\n";


$channel->close();
$connection->close();

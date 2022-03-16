<?php

require_once '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FibonacciRpcClient
{
    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;

    //构造函数，监听回调队列，处理
    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            'localhost',
            5672,
            'root',
            'root'
        );
        $this->channel = $this->connection->channel();
        //1.生成回调队列
        $this->callback_queue = 'reply_to';
        $this->channel->queue_declare($this->callback_queue, false, true, false, false);

//        list($this->callback_queue, ,) = $this->channel->queue_declare(
//            "",
//            false,
//            false,
//            true,
//            false
//        );


        //2.1.轮训消费
        $this->channel->basic_consume(
            $this->callback_queue,
            '',
            false,
            true,
            false,
            false,
            array(
                $this,
                'onResponse'
            )
        );

//        echo "consume is async" . PHP_EOL;


    }

    //2.1.2监听队列的回调函数
    public function onResponse($rep)
    {
        if ($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    //远程调用,发送消息至rpc队列
    public function call($n)
    {
        $this->response = null;
        $this->corr_id = uniqid();//3.生成请求的唯一标识

        //4.1.创建消息，携带请求标识、回调队列名称
        $msg = new AMQPMessage(
            (string)$n,
            array(
                'correlation_id' => $this->corr_id,
                'reply_to'       => $this->callback_queue
            )
        );
        //4.2.发送消息至rpc队列，等待服务端消费
        $this->channel->basic_publish($msg, '', 'rpc_queue');
        //5.循环判断结果
        while (!$this->response) {
            $this->channel->wait();
        }
        return intval($this->response);
    }
}

$fibonacci_rpc = new FibonacciRpcClient();//构造函数，监听回调队列reply_to
$response = $fibonacci_rpc->call(35);//发送消息至prc队列，并循环判断回调队列的处理结果。
echo ' [.] Got ', $response, "\n";//回调队列的处理结果

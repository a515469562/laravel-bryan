<?php


namespace App\Service;


class AmqpService
{
    private $config;
    private $connection;
    private $channel;

    public function __construct($config = [])
    {
        if (empty($config)){
            $config = [
                'host' => '127.0.0.1',
                'vhost' => '/',
                'port' => '5672',
                'login' => 'guest',
                'password' => 'guest'
            ];
        }
        $this->config = $config;
    }

    /**
     * 建立连接
     * @return array
     */
    public function connection(){
        if (!empty($this->connection)){
            return ['status' => true, 'data' => $this->connection];
        }
        $errMsg = '';
        $config = $this->config;
        try {
            $connection = new \AMQPConnection($config);
            if (!$connection->connect()){
                return ['status' => false, 'msg' => $errMsg];
            }
            $this->connection = $connection;
            return ['status' => true, 'data' => $connection];
        } catch ( \AMQPConnectionException $e){
            $errMsg = $e->getMessage();
        }
        return ['status' => false, 'msg' => $errMsg];
    }

    /**
     * 建立信道
     */
    public function channel(){
        if (!empty($this->channel) ){
            return ['status' => true, 'data' => $this->channel];
        }
        $connection = $this->connection();
        if (!$connection['status']){
            return $connection;
        }
        $connection = $connection['data'];
        try {
            $cn = new \AMQPChannel($connection);
            $this->channel = $cn;
        }catch (\AMQPChannelException $e){
            return ['status' => false, 'msg' => $e->getMessage()];
        }
        return ['status' => true, 'data' => $cn];
    }

    public function exchange($exchangeName, $type = AMQP_EX_TYPE_DIRECT, $flags = AMQP_DURABLE){
        $cn = $this->channel();
        if (!$cn['status']){
            return $cn;
        }
        $cn = $cn['data'];
        $ex = new \AMQPExchange($cn);
        $ex->setName($exchangeName);
        $ex->setType($type);
        $ex->setFlags($flags);
        try {
            $ex->declareExchange();
            return ['status' => true, 'data' => $ex];
        }catch (\AMQPExchangeException $e) {
            $errMsg = $e->getMessage();
        } catch (\AMQPChannelException $e) {
            $errMsg = $e->getMessage();
        } catch (\AMQPConnectionException $e) {
            $errMsg = $e->getMessage();
        }
        return ['status' => true, 'msg' => $errMsg];
    }

    /**
     * 生产者发布消息
     * @param string $exchangeName
     * @param string $routingKey
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function producer($exchangeName = 'exchange_1', $routingKey = 'route_key_1'){
       $ex = $this->exchange($exchangeName);
       if (!$ex['status']){
           return $ex;
       }
       $ex = $ex['data'];
        for ($i = 0; $i < 3; $i++) {
            $msg = [
                'data' => "消息：{$i}"
            ];
            //消息发布
            $ex->publish(json_encode($msg, JSON_UNESCAPED_UNICODE), $routingKey, AMQP_NOPARAM );
        }
        //断开连接
        $this->channel->close();
        $this->connection->disconnect();

        return ['status' => true, 'data' => '生产者发布成功'];
    }

    /**
     * 消费消息
     * @param string $exchangeName
     * @param string $routingKey
     * @return array
     */
    public function consumer($exchangeName = 'exchange_1', $routingKey = 'route_key_1'){
        $cn = $this->channel();
        if (!$cn['status']){
            return $cn;
        }
        $cn = $cn['data'];
        $ex = $this->exchange($exchangeName);
        if (!$ex['status']){
            return $ex;
        }
        $ex = $ex['data'];
        try {
            $queue = new \AMQPQueue($cn);
            $queue->setName('queue_1');
            $queue->setFlags(AMQP_DURABLE);
            $queue->declareQueue();
            $queue->bind($ex->getName(), $routingKey);//队列绑定到交换机上指定routing_key
            $queue->consume(function ($envelop){
                var_dump($envelop->getBody());
            });
            var_dump('消费结束');
        }catch (\AMQPChannelException $e) {
            $errMsg = $e->getMessage();
        }catch (\AMQPConnectionException $e) {
            $errMsg = $e->getMessage();
        } catch (\AMQPEnvelopeException $e) {
            $errMsg = $e->getMessage();
        }catch (\Exception $e){
            $errMsg = $e->getMessage();
        }

        if (!empty($errMsg)){
            dd('消费失败：'.$errMsg);
        }
    }
}

<?php


namespace App\Service;


use Monolog\Handler\IFTTTHandler;

class SingleTest
{
    //单例模式
    private static $instance;

    private $connection;

    private function __construct()
    {
        $connection = (new AmqpService() )->connection()['data'];//实际上对象必须在单例模式类中唯一存在
        $this->connection = $connection;
    }

    private function __clone()
    {
    }

    public static function getInstance(){
        if (!(self::$instance instanceof self)){
           self::$instance = new self();
        }
        return self::$instance;
    }

    public function disConnect(){
        if ($this->connection instanceof \AMQPConnection){
            var_dump('关闭amql连接');
            return $this->connection->disconnect();
        }
        return false;
    }




}

<?php

//1.1.计算类的抽象接口
interface CalC
{
    public function getTwoVal($a, $b);
}

//1.2.面向接口编程，通过实现接口来扩展具体的功能类
class AddC implements CalC
{
    public function getTwoVal($a, $b)
    {
        return $a + $b;
    }
}

class SubC implements CalC
{

    public function getTwoVal($a, $b)
    {
        return $a - $b;
    }
}

class MultiplyC implements CalC
{
    public function getTwoVal($a, $b)
    {
        return $a * $b;
    }
}

//1.3.工厂类管理具体计算类的创建
class Factory
{
    public static function getObj($operator)
    {
        switch ($operator) {
            case '+':
                $cal = new AddC();
                break;
            case '-':
                $cal = new SubC();
                break;
            case '*':
                $cal = new MultiplyC();
                break;
            default:
                $cal = new AddC();
        }
        return $cal;
    }
}

//使用
//$add = Factory::getObj('+');
//$res = $add->getTwoVal(5, 2);
//echo $res;

//2.单例模式，三私一公两静态
//class Db
//{
//    private static $instance;
//
//    private function __construct()
//    {
//        //内部初始化
//    }
//
//    private function __clone()
//    {
//        //禁止克隆
//    }
//
//    //开放单一入口
//    public static function getDb()
//    {
//        //判断是否已存在
//        if (!(self::$instance instanceof self)) {
//            self::$instance = new Db();
//        }
//        return self::$instance;
//    }
//}
////使用
//$db = Db::getDb();
//$db1 = Db::getDb();
//$res = ($db === $db1);
//var_dump($res);


//3.观察者模式
interface Observer
{
    //观察者的方法
    function onChanges($sender, $args);
}

interface Observable
{
    //注册观察者
    function addObserver($observer);
}


class UserList implements Observable
{
    private $observers;

    //可观察的类，添加其观察者
    public function addObserver($observer)
    {
        $this->observers[] = $observer;
    }

    //执行方法，并通知观察者
    public function addCustomer($name)
    {
        foreach ($this->observers as $observer) {
            $observer->onChanges($this, $name);//通知观察者，传递参数和发送人信息
        }
    }
}

class UserListLogger implements Observer
{
    //观察者接受参数，并执行操作
    function onChanges($sender, $args)
    {
        echo "add {$args} to user_list" . PHP_EOL;
    }
}

//使用
//$logger = new UserListLogger();//观察类
//
//$userList = new UserList();//可观察类
//$userList->addObserver($logger);//添加观察类
//$userList->addCustomer('Bryan');//执行动作

//4.策略模式，以计算类为例
interface CalStrategy
{
    function getVal($a, $b);
}

class AddStrategy implements CalStrategy
{
    function getVal($a, $b)
    {
        return $a + $b;
    }
}

class SubStrategy implements CalStrategy
{
    function getVal($a, $b)
    {
        return $a - $b;
    }
}

class MultiplyStrategy implements CalStrategy
{
    function getVal($a, $b)
    {
        return $a * $b;
    }
}

class CalContext
{
    private $strategy;

    //初始化策略
    public function __construct(CalStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    //用于策略变更
    public function setStrategy(CalStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function getVal($a, $b)
    {
        return $this->strategy->getVal($a, $b);
    }
}

//使用
$calContext = new CalContext(new AddStrategy());//初始化策略
$addResult = $calContext->getVal(5, 10);
echo $addResult . PHP_EOL;


$calContext->setStrategy(new MultiplyStrategy());//变更策略
$mulResult = $calContext->getVal(5, 10);
echo $mulResult . PHP_EOL;




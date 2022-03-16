<?php


namespace App\Service\Origin;


class Stack
{
    private $stack;

    //构造函数（魔术函数）
    public function __construct(array $array =[])
    {
        $this->stack = $array;
    }

    //查看
    public function getStack(){
        return $this->stack;
    }

    //入栈
    public function inputData($data){
        return array_push($this->stack, $data);
    }

    //出栈
    public function outputData(){
        return array_pop($this->stack);
    }

    //魔术函数call，方法不存在时
    public function __call($name, $arguments)
    {
        var_dump("方法{$name}不存在或参数错误");

        // TODO: Implement __call() method.
    }


}

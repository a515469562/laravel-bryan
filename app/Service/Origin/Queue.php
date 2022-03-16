<?php


namespace App\Service\Origin;


class Queue
{
    private $queue;

    public function __construct(array $arr=[])
    {
        $this->queue = $arr;
    }

    public function getQueue(){
        return $this->queue;
    }

    public function inputData($data){
        return array_push($this->queue, $data);

    }

    public function outputData(){
        return array_shift($this->queue);
    }

}

<?php


namespace App\Service\Generator;


use Generator;

class Task {
    protected $taskId;//任务id
    protected $coroutine;//生成器
    protected $sendValue = null;//生成器send值
    protected $beforeFirstYield = true;//迭代指针是否是第一个

    public function __construct($taskId, Generator $coroutine) {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }

    public function getTaskId() {
        return $this->taskId;
    }

    /**
     * 设置插入数据
     * @param $sendValue
     */
    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }

    /**
     * send数据进行迭代
     * @return mixed
     */
    public function run() {
        //如果是
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            var_dump($this->coroutine->current());
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = null;
            return $retval;
        }
    }

    /**
     * 是否完成
     * @return bool
     */
    public function isFinished() {
        return !$this->coroutine->valid();
    }
}

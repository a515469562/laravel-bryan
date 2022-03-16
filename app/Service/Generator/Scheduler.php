<?php


namespace App\Service\Generator;


use Generator;
use SplQueue;

class Scheduler {
    protected $maxTaskId = 0;//任务id
    protected $taskMap = []; // taskId => task
    protected $taskQueue;//任务队列

    public function __construct() {
        $this->taskQueue = new SplQueue();
    }

    public function newTask(Generator $coroutine) {
        $tid = ++$this->maxTaskId;
        //新增任务
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    /**
     * 任务入列
     * @param Task $task
     */
    public function schedule(Task $task) {
        $this->taskQueue->enqueue($task);
    }

    public function run() {
        while (!$this->taskQueue->isEmpty()) {
            //任务出列进行遍历生成器数据
            $task = $this->taskQueue->dequeue();
            $task->run();

            if ($task->isFinished()) {
                //完成则删除该任务
                unset($this->taskMap[$task->getTaskId()]);
            } else {
//                继续入列
                $this->schedule($task);
            }
        }
    }
}

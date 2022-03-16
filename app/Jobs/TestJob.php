<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $orderId;

    /**
     * Create a new job instance.
     *
     * @param string $orderId
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        重试机制避免网络原因
//        超时时间设置， retry_after要大于超时时间
        $attemptMax = 3;//重新请求尝试次数
        $delaySeconds = 10;//延时处理秒
        //失败处理, 根据业务场景：删除失败任务，或者告知用户，定时任务检测重试...
        //1需要吧相关数据放入特定的死信队列对应表，
        //2服务器通过定时任务周期性检查该表来重新推送
        if ($this->attempts() >= $attemptMax){
            var_dump("重新尝试请求{$this->attempts() }次，记录失败");
            $this->release($delaySeconds);//进入延时队列，  延时时间后处理, 失败后处理次数取决于剩下的tries次数
        }else{
            var_dump("现在是第{$this->attempts()}次重新尝试");
        }



        $job = $this->job;
        $connection = $job->getConnectionName();
        $jobId = $job->getJobId();
        $jobName = $job->getName();
        $queueName = $job->getQueue();
        $body = $job->getRawBody();
        $remark = compact('connection','jobId','jobName','queueName','body');
        $insert = [
            'order_id' => $this->orderId,
            'remark' => json_encode($remark)
        ];
        Order::query()->insert($insert);

    }
}

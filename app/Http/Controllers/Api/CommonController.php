<?php


namespace App\Http\Controllers\Api;


use App\Facades\MysqlMg;
use App\Http\Controllers\Controller;
use App\Jobs\TestJob;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\User;
use App\Service\AmqpService;
use App\Service\Generator\Scheduler;
use App\Service\Origin\Mysql;
use App\Service\Origin\MysqlManager;
use App\Service\SingleTest;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use mysqli;
use PhpParser\Error;
use PhpParser\Node\Stmt\DeclareDeclare;
use phpseclib3\Crypt\Random;
use SplQueue;

class CommonController extends Controller
{
    const GUARD = 'api';

    //laravel消息队列demo
    public function queueTest()
    {
        try {
            $orderId = "J" . random_int(10, 1000);
            $res = TestJob::dispatch($orderId)
                ->onConnection('redis')
                ->onQueue('default');
//            var_dump($res);
        } catch (\Exception $e) {
            return self::apiError("发生异常：{$e->getMessage()}");
        }

        return self::apiSuccess("入队成功");
    }

    /**
     * 注册
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function register()
    {
        $data = request()->only('name', 'password', 'email');
        $name = $data['name'] ?? '';
        $password = $data['password'] ?? '';
        $email = $data['email'] ?? '';
        if (empty($name) || empty($password)) {
            return self::apiError("缺失参数");
        }
        $password = Hash::make($password);
        $condition = [
            'name'     => $name,
            'password' => $password,
            'email'    => $email
        ];
        try {
            $result = User::query()->insert($condition);
        } catch (\Exception $e) {
            return self::apiError("注册失败:{$e->getMessage()}");
        }
        return self::apiSuccess($result);

    }

    /**
     * 登录
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login()
    {
        $guard = self::GUARD;
        $credentials = request()->only(['email', 'password', 'name']);
        $paramsCount = 2;
        //name或者email登录
        if (empty($credentials) || count($credentials) < $paramsCount) {
            return self::apiError('参数错误');
        }
        $token = Auth::guard($guard)->attempt($credentials);
        if ($token) {
            $user = Auth::guard($guard)->user()->toArray();
            $expire = date('Y-m-d H:i:s', time() + env('JWT_TTL') * 60);
            $data = [
                'user_info' => $user,
                'token'     => env('JWT_PREFIX', 'Bearer ') . $token,
                'expire_in' => $expire
            ];

            return self::apiSuccess($data);
        } else {
            return self::apiError('登录失败');
        }

    }

    //redis悲观锁防止重复提交
    public function redisLock()
    {
        $redis = new \Redis();//使用php的redis扩展
        $redis->connect("127.0.0.1", 6379);
        $key = 'phone_order_id';//请求的标识参数
        $lock = $redis->set($key, 1, ['nx', 'ex' => 10]);//利用redis的setnx上锁并设置超时时间防止异常死锁
//
//        if ($lock) {
//            sleep(2);
//            var_dump("加锁后操作业务..");
//            $redis->del($key);
//            var_dump("业务处理完毕，释放锁完毕");
//        } else {
//            //非阻塞
//            dd("请勿重复操作");
//        }
    }

    //订单重复支付
    public function repeatClick()
    {
        //下单流程
        $preStatus = Order::NO_PAY;
        $afterStatus = Order::PAID;
        $userId = $_POST['user_id'] ?? 0;
        $orderId = $_POST['order_id'] ?? '';
        if (empty($userId) || empty($orderId)) {
            return self::apiError("缺少参数");
        }
        $orderUpCond = [
            'user_id'      => $userId,
            'order_status' => $afterStatus
        ];

        DB::beginTransaction();
        try {
            $order = Order::query()->where('order_id', '=', $orderId)->select('price')->first();
            if (empty($order)) {
                return self::apiError("订单不存在");
            }
            $price = $order->price;
            //1、修改订单状态
            //乐观锁，保证并发下只有一个更新成功；
            $updateOrder = Order::query()->where('order_id', '=', $orderId)
                ->where('user_id', '=', $userId)//避免查询是否是该用户订单
                ->where('order_status', '=', $preStatus)//并发下若已经发生改变则修改失败
                ->update($orderUpCond);
//            sleep(2);
            if (!$updateOrder) {
                return self::apiError("订单有误");
            }

            //修改账户余额
            $updateUser = DB::update("update users set balance=balance-{$price} where id='{$userId}' ");
//            var_dump("update_user:{$updateUser}");
            //2、新增订单日志
            $orderLogInsert = [
                'order_id'     => $orderId,
                'pre_status'   => $preStatus,
                'after_status' => $afterStatus,
                'op_amount'    => $price,
                'created_at'   => date('Y-m-d')
            ];
            OrderLog::query()->insert($orderLogInsert);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("支付失败：{$e->getMessage()}");
            return self::apiError("出现异常，支付失败");
        }
        DB::commit();
        return self::apiSuccess("订单支付成功");

    }

    //乐观锁，版本控制
    public function opLock()
    {
        $userId = request()->get('user_id');
        $orderNum = 1;
        $con = true;
        $i = 0;
        $order = Order::query()->where('id', '=', $orderNum)->first();
//        sleep(3);
        while ($con) {
            if (!empty($order)) {
                $update = [
                    'user_id' => $userId,
                    'version' => $order->version + 1
                ];
                //并发下的多个请求同时更改相同记录时，通过版本号保证只有一个请求更改成功，解决更新冲突
                $update = Order::query()->where('version', '=', $order->version)->update($update);
                if ($update) {
                    $con = false;
                    return apiSuccess("修改成功，版本：{$order->version}, 尝试{$i}次");
                } else {
                    $i++;
                    var_dump("修改失败第{$i}次");
                    $order = Order::query()->where('id', '=', $orderNum)->first();
                    //可以增加抢购记录，或者直接失败；
                }
            }
        }

    }


    //悲观锁测试
    public function psLock()
    {
        //1.基于文件锁的悲观锁
        $guard = self::GUARD;
        $fp = fopen(storage_path('logs/laravel.log'), 'r');
        if (flock($fp, LOCK_EX)) {
//            flock($fp,LOCK_EX);//加排它锁
            sleep(4);
            try {
                $user = Auth::guard($guard)->user();
                Redis::setex('bryan', 60, "{$user['name']}'s email is {$user['email']}");
                $res = Redis::get('bryan');
                flock($fp, LOCK_UN);//释放锁
                fclose($fp);
                return self::apiSuccess($res);
            } catch (\Exception $e) {
                return self::apiError('redis异常：' . $e->getMessage());
            }

        } else {
//            flock($fp,LOCK_EX | LOCK_NB )
            return self::apiError('LOCK_NB使文件锁不阻塞，返回已锁定结果');
        }
        //2.基于redis的悲观锁
//        $cond = true;
//        do{
//            $lock = Redis::get('lock');
//            if (empty($lock)){
//                Redis::setex('lock',10, 1);//上锁，设置超时时间避免死锁,超时时间必须大于执行时间才能保证悲观锁效果
////                sleep(5);
//                $user = Auth::guard($guard)->user();
//                Redis::setex('bryan', 60, "{$user['name']}'s email is {$user['email']}");
//                $res = Redis::get('bryan');
//                $cond = false;
//                Redis::del('lock');//解锁
//            }
//        }while($cond);
//        return self::apiSuccess($res) ;
    }

    public function testMysql()
    {
        $guard = self::GUARD;
        try {
            $user = Auth::guard($guard)->user();
            return self::apiSuccess($user);
        } catch (\Exception $e) {
            return self::apiError('mysql异常:' . $e->getMessage());
        }

    }

    //测试死锁：事务A-资源1=》事务B-资源2=》事务A-资源2=》事务B-资源1
    //死锁原因： 事务对资源(行记录)的加锁顺序不同；
    //注意点： 资源的筛选条件必须能够唯一标识才会加锁【主键索引/唯一索引】
    public function deadlockTest()
    {
        DB::beginTransaction();
        try {
            Order::query()->where('user_id', 1)->update(['price' => 110.1]);
            sleep(5);
            Order::query()->where('user_id', 2)->update(['price' => 220.2]);
        } catch (\Exception $e) {
            DB::rollBack();
            $errMsg = $e->getMessage();
            return apiError($errMsg);
        }

        DB::commit();
        $result = '完成操作';
        return self::apiSuccess($result);
    }

    public function deadlockTest2()
    {
        DB::beginTransaction();
        try {
            Order::query()->where('user_id', 2)->update(['price' => 440.4]);
            sleep(5);
            Order::query()->where('user_id', 1)->update(['price' => 330.3]);
        } catch (\Exception $e) {
            DB::rollBack();
            $errMsg = $e->getMessage();
            return apiError($errMsg);
        }
        DB::commit();
        $result = '完成操作2';
        return self::apiSuccess($result);

    }

    /**
     * RabbitMq生产者
     */
    public function producer()
    {
        $service = new AmqpService();
        $result = $service->producer();
        if (!$result['status']) {
            return self::apiError($result['msg']);
        }
        return self::apiSuccess($result['data']);
    }

    /**
     * @return \Generator
     */
    function gen()
    {
        yield 1;//generator->current
        echo '成功yield1';//generator->next开始位置
        yield 2;
        echo '成功yield2';
        yield 3;
        echo '成功yield3';
    }


    /**
     *
     */
    public function singleTest()
    {

        $iterator = $this->gen();//初始化生成器

        var_dump($iterator->valid());//检验是否有效
        var_export($a = $iterator->current());//当前值
        $iterator->next();//下一个

        var_dump($iterator->valid());
        var_export($iterator->current());
        $iterator->next();

        var_dump($iterator->valid());
        var_export($iterator->current());
        $iterator->next();


        var_dump($iterator->valid());
        var_export($iterator->current());
        $iterator->next();
        die;

        var_dump($res = $this->yieldTask1()->current());
        var_dump($res = $this->yieldTask1()->next());
        var_dump($res = $this->yieldTask1()->next());
        die;
//        $singleTest1 = SingleTest::getInstance();
//        $singleTest2 = SingleTest::getInstance();
////        $disconnect = $singleTest1->disconnect();//php内存回收机制每次请求后会自动销毁对象
//        var_dump($singleTest1);
//        echo('<br/>');
//        var_dump($singleTest2);
//        echo $singleTest2 === $singleTest1? '单例' : '非单例';


        //原生mysql单例测试
//        $mysql = Mysql::instance();
//        $whereId = $_GET['id'] ?? 2;
//        //占位符
//        $query = "SELECT id,order_id,product_name FROM laravel.orders where id>'{$whereId}'  limit 10";
//        $result = $mysql->query($query);
//        echo json_encode($result,JSON_UNESCAPED_UNICODE);die;
//        return $result;

//        $query='select * from orders limit 10';
//        IOC容器解析使用
//        $mysql = new MysqlManager(Mysql::instance());

//        var_dump( $this->test1(null)  );

        //回调测试
        //匿名函数作为回调事件参数
//        $callback = function () {
//            $this->echoTime();
//        };
//        $this->callbackTest($callback);
//        die;


        //模拟任务交叉执行
//        $this->yieldTest1();
        //利用yield协程实现任务交叉
        $this->yieldTest2();
        die;

        $mysql = app()->make(MysqlManager::class);

        var_dump($mysql);
        die;
//        $result = $mysql->getQuery($query);
        $result = $mysql->getQuery($query);
        var_dump($result);

        //自定义Facade使用
        $result = MysqlMg::getQuery($query);
//        var_dump($result);die;


    }

    //使用使参数为null或者int， 不如int $a=null
    function test1(?int $a)
    {
        return $a;
    }

    //文件导出
    public function bigFileExport()
    {
        try {
            //1.上传文件保存
//            $file = $_FILES['file'];
//            $fileName = $file['name'];
//            $filePath = $file['tmp_name'];
//            $url = storage_path()."/app/public/{$fileName}";
//            $res = move_uploaded_file($filePath,$url);
            //2.生成浏览器导出csv文件
            $orders = OrderLog::query()->select()->get();
            $max = 100;
            $date = date('YmdHis');
            $fileName = "order_{$date}.csv";
            header("Content-type: text/csv");//内容形式，下载保存还是页面显示
            header("Content-Disposition: attachment; filename={$fileName}");//定义文件名

            //手动缓冲，避免大文件一次性传完才缓冲
            ob_end_clean();
            ob_start();
            $resource = fopen('php://output', 'w');//导出文件
            fwrite($resource, chr(0xEF) . chr(0xBB) . chr(0xBF));//解决文件乱码，利用bom设置utf-8编码方式

            $fields = ['id', 'order_id', 'pre_status', 'after_status', 'op_amount', 'created_at', 'updated_at'];
            fputcsv($resource, $fields);//csv首行
            for ($i = 0; $i < $max; $i++) {
                foreach ($orders->toArray() as $item) {
                    fputcsv($resource, $item);
                }
            }

            ob_flush();
            flush();
            ob_end_clean();
            fclose($resource);
        } catch (\Exception $exception) {
            return self::apiError('发生异常:' . $exception->getMessage());
        }

//        $resultUrl =  $_SERVER['SERVER_NAME'] . '/storage/' . $fileName;
//        $resultUrl = '../storage/' . $fileName;
//        return self::apiSuccess($resultUrl);

    }

    /**
     * 图像处理-水印
     */
    public function imageMerge()
    {
//        printf([1,2,3]);
        $arr = [1, 2, 3];
        print_r($arr);
        var_dump($arr);
        die;
        $imgObj = imagecreatefromjpeg('/Users/bryanzhou/Desktop/图片/IMG_0331.jpg');
        $waterMark = imagecreatefromjpeg("/Users/bryanzhou/Desktop/图片/仟传icon.jpeg");
        imagecopymerge($imgObj, $waterMark, 20, 20, 50, 50, 100, 100, 50);
        //生成新图片
        $newFile = '/Users/bryanzhou/Desktop/图片/hah.jpg';
        $res = imagejpeg($imgObj, $newFile);
        return self::apiSuccess($newFile);
    }

    /**
     * 回调/闭包函数测试
     * @param $callback
     */
    function callbackTest(callable $callback)
    {
        $i = 0;
        while (true) {
            $i++;
            call_user_func($callback);
            if ($i >= 5) {
                break;
            }
        }

    }

    function echoTime()
    {
        echo microtime() . PHP_EOL;
    }


    /**
     * 交替执行任务
     */
    function yieldTest1()
    {
        $task1Result = true;
        $task2Result = true;
        $i = 0;
        while (true) {
            //交替执行，任务的调度权重及顺序是固定的，不够灵活。
            $task1Result && $task1Result = $this->task1($i);
            $task2Result && $task2Result = $this->task2($i);
            $i++;
            if ($task1Result == false && $task2Result == false) {
                break;
            }
        }
    }

    function task1(int $i)
    {
        if ($i < 5) {
            usleep(9000);
            echo "task1:任务编号{$i}" . PHP_EOL;
            return true;
        }
        return false;
    }

    function task2(int $i)
    {
        if ($i < 10) {
            usleep(3000);
            echo "task2:任务编号{$i}" . PHP_EOL;
            return true;
        }
        return false;
    }

    /**
     * @param int $n
     * //     * @return \Generator
     */
    function yieldTask1(int $n = 50)
    {
        for ($i = 0; $i < $n; $i++) {
            usleep(1000);
            echo "task1:任务编号{$i}" . PHP_EOL;
//            return $i;
            yield $i;//程序不会终止，而是以协程的方式执行：
        }
    }

    /**
     * @param int $n
     * @return \Generator
     */
    function yieldTask2(int $n = 100)
    {
        for ($i = 0; $i < $n; $i++) {
            usleep(1000);
            echo "task2:任务编号{$i}" . PHP_EOL;
            yield $i;
        }
    }

    /**
     * 协程测试
     */
    function yieldTest2()
    {
        $scheduler = new Scheduler();
        $scheduler->newTask($this->yieldTask1());
        $scheduler->newTask($this->yieldTask2());

        $scheduler->run();
    }


}

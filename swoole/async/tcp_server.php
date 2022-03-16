<?php

//创建server对象，监听所有ip的 9501端口
$server = new Swoole\Server('0.0.0.0', 9501);

//设置异步任务的工作进程数量
$server->set([
    'task_worker_num' => 4
]);

//监听连接进入事件
//$fd客户端唯一标识
$server->on('Connect', function ($server, $fd){
    echo "Client:connect\n";
});

//监听数据接收事件
$server->on('Receive', function ($server, $fd, $reactor_id, $data){
    //投递异步任务
    $taskId = $server->task($data);
    $server->send($fd, "Server:{$data}");
});

//处理异步任务(此回调函数在task进程中执行)
$server->on('task', function ($server, $task_id, $reactor_id, $data){
   echo "New AsyncTask[id={$task_id}]" . PHP_EOL ;
   //返回任务执行的结果
    $server->finish("{$data} -> OK");//操作可选
});

//处理异步任务的结果（此回调函数在worker进程中执行）
$server->on('Finish', function ($server, $taskId, $data){
    echo "AsyncTask[{$taskId}] Finish:{$data}`" . PHP_EOL;
});

//监听连接关闭事件
$server->on('Close', function ($server, $id){
    echo "Client:close.\n";
});

$server->start();
//var_dump($server);

<?php


/**
 * @return \Generator
 */

//function nums() {
//    for ($i = 0; $i < 5; ++$i) {
//        //get a value from the caller
//        $cmd = (yield $i);
//
//        if($cmd == 'stop')
//            return;//exit the function
//    }
//}
//
//$gen = nums();
//foreach($gen as $v)
//{
//    if($v == 3)//we are satisfied
////        var_dump('send stop');
//        $gen->send('stop');
//
//    echo "{$v}\n";
//}


function gen() {
    $ret = (yield 'yield1');
    var_dump($ret);
    $ret = (yield 'yield2');
//    var_dump($ret);
}
$g = gen();
var_dump($g->current());
var_dump($g->send('ret1'));
//output:
//string(6) "yield1"
//string(4) "ret1"
//string(6) "yield2"


//function gen()
//{
//    yield 1;//generator->current
//    echo '开始迭代2' . PHP_EOL;//generator->next开始位置
//    yield 2;
//    echo '无可迭代元素' . PHP_EOL;
//
//}

//$iterator = gen();
//var_dump($iterator->valid());//检验是否有效，（true）
//var_dump($iterator->current());//当前值，（1）
//$iterator->next();//下一个（开始迭代2）
//
//var_dump($iterator->valid());//（true）
//var_dump($iterator->current());//（2）
//$iterator->next();//（开始迭代3）
//
//var_dump($iterator->valid());//（false）
//var_export($iterator->current());//NULL
//$iterator->next();
//die;

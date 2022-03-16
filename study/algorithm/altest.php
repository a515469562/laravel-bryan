<?php

//require_once __DIR__ . '/app/Helpers/functions.php';
//ini_set('display_errors', 'Off');

define("FIBONACCI_NUM", 10);

$arr = [-1, 0, 12, 31, 100, 992];
//$arr = [99,100,200];

foreach ($arr as $item) {
    $res = fibSearch($arr, $item);
    var_dump($res);
}
die;

//for ($i = 1; $i <= 10; $i++) {
//$res = fioNum($i);
//    var_dump($res);
//}

die;

/**
 * 斐波那契查找
 * @param array $arr
 * @param int $val
 * @return bool|int|mixed
 * @throws Exception
 */
function fibSearch(array $arr, int $val)
{
    $len = count($arr);
    //数组长度小于3，直接用顺序查找
    if ($len < 3) {
        foreach ($arr as $k => $v) {
            if ($v == $val) {
                return $k;
            }
        }
        return -1;
    }
    $left = 0;
    $right = $len - 1;
    $fibArr = [];//斐波那契数列
    $k = 0;//斐波那契数列最小值初始化
    try {
        //1.构造斐波那契数列
        $fibNum = 0;
        while ($len > $fibNum) {
            //2.寻找大于等于数组长度的最小值k
            $k++;
            $fibNum = fioNum($k);
            $fibArr[] = $fibNum;
        }
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
    //3.对多余部分进行最大值填充
    for ($i = $len - 1; $i < $fibNum; $i++) {
        $arr[$i] = $arr[$right];
    }
    $k--;//数组下标从0开始，所以要减1
    //4.进行黄金分割查找
    while ($left <= $right) {
        $mid = $left + $fibArr[$k - 1] - 1;//确定黄金分割位置，长度-1表示间隔距离
        if ($arr[$mid] == $val) {
            //判断是否是填充数据
            if ($mid > $right) {
                return $right;
            } else {
                return $mid;
            }
        } elseif ($arr[$mid] > $val) {
            //在左边
            $right = $mid - 1;
            $k -= 1;//左边区域长度F(k-1)
        } else {
            //在右边
            $left = $mid + 1;
            $k -= 2;//右边区域长度F(k-2)
        }
    }
    return -1;//没找到
}

/**
 * 获取斐波那契数列值
 * F(1)=F(2)=1,F(k)=F(k-1)+F(k-2),k>=3
 * @param int $k
 * @return int|void
 * @throws Exception
 */
function fioNum(int $k = FIBONACCI_NUM)
{
    if ($k < 1) {
        throw new Exception('k必须为大于0的整数');
    }
    if ($k == 1 | $k == 2) {
        return 1;//数列初始值
    }
    return fioNum($k - 1) + fioNum($k - 2);
}


function interpolationSearch(array $arr, int $val)
{
    $len = count($arr);
    $left = 0;
    $right = $len - 1;
    $result = -1;
    //边界处理
    if ($val < $arr[$left] || $val > $arr[$right]) {
        return $result;
    }
    while ($left < $right) {
        $mid = $left + abs(($val - $arr[$left]) * ($right - $left) / ($arr[$right] - $arr[$left]));//注意绝对值
        $mid = intval($mid);
        if ($arr[$mid] == $val) {
            return $mid;//返回元素下标
        } elseif ($arr[$mid] > $val) {
            //左侧
            $right = $mid - 1;
        } else {
            //右侧
            $left = $mid + 1;
        }
    }
    return -1;//未找到
}

function binarySearch(array $arr, int $val)
{
    $len = count($arr);
    $left = 0;//初始区间左下标
    $right = $len - 1;//初始区间右下标
    $result = -1;
    //越界处理
    if ($val > $arr[$right] || $val < $arr[$left]) {
        return $result;
    }
    //边界处理
    if ($arr[$left] == $val) {
        return $left;
    } elseif ($arr[$right] == $val) {
        return $right;
    } else {
        //开始二分查找
        while ($left <= $right) {
            $mid = $left + intval(($right - $left) / 2);//取中间值
            if ($arr[$mid] == $val) {
                return $mid;//返回待查值下标位置
            } elseif ($arr[$mid] > $val) {
                //值在左边
                $right = $mid - 1;
            } else {
                //值在右边
                $left = $mid + 1;
            }
        }
    }
    return $result;//未找到
}


function insertSort(array $arr)
{
//    var_dump("比较插入次数：" . PHP_EOL);
//    $time = 0;
    $n = count($arr);
    for ($i = 1; $i < $n; $i++) {
        $preIndex = $i - 1;
        $current = $arr[$i];//默认最大值，升序
        //当前值与已排序数组，遍历比较
//        $time += 1;
//        var_dump("比较第{$time}次");
        //最好情况只扫描一次
        while ($preIndex >= 0 && $current < $arr[$preIndex]) {
//            $time += 2;
//            var_dump("比较移动第{$time}次");
            $arr[$preIndex + 1] = $arr[$preIndex];//右移替换，当前位置待比较
            $preIndex--;//已排序数组游标，往前
        }
        //发生过右移,插入右移空出的位置
        $arr[$preIndex + 1] = $current;
    }
    return $arr;


}

function bucketSort(array $arr, int $bucketSize = BUCKET_SIZE)
{
    $bucket = [];
    $result = [];
    //确定最值
    $max = $arr[0];
    $min = $arr[0];
    foreach ($arr as $item) {
        if ($max < $item) {
            $max = $item;
        }
        if ($min > $item) {
            $min = $item;
        }
    }
    //获取桶个数
    $bucketCount = intval($max - $min / $bucketSize) + 1;
    //数组元素入桶
    foreach ($arr as $item) {
        $key = intval(($item - $min) / $bucketCount);//根据映射关系每个桶存储相应元素
        $bucket[$key][] = $item;
    }
    //对每个桶进行排序，并放入结果
    foreach ($bucket as $item) {
        $item = insertSort($item);//采用插入排序对桶内元素排序
        //放入最终结果集
        foreach ($item as $value) {
            array_push($result, $value);
        }
    }
    return $result;
}


function getCatalogue()
{
    return [
        ['id' => 1, 'pid' => 0, 'name' => '一级分类1'],
        ['id' => 2, 'pid' => 0, 'name' => '一级分类2'],
        ['id' => 3, 'pid' => 0, 'name' => '一级分类3'],
        ['id' => 4, 'pid' => 1, 'name' => '二级分类1-1'],
        ['id' => 5, 'pid' => 1, 'name' => '二级分类1-2'],
        ['id' => 6, 'pid' => 2, 'name' => '二级分类2-1'],
        ['id' => 7, 'pid' => 2, 'name' => '二级分类2-2'],
        ['id' => 8, 'pid' => 3, 'name' => '二级分类3-1'],
        ['id' => 9, 'pid' => 3, 'name' => '二级分类3-2'],
        ['id' => 10, 'pid' => 4, 'name' => '三级分类1-1-1']
    ];
}

//数组位置交换
function swp(&$arr, $i, $k)
{
    $tmp = $arr[$i];
    $arr[$i] = $arr[$k];
    $arr[$k] = $tmp;
}

//1冒泡排序
function bubSort($arr)
{
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($k = 0; $k < $n - 1 - $i; $k++) {
            //升序
            if ($arr[$k] > $arr[$k + 1]) {
                swp($arr, $k, $k + 1);
            }
        }
    }
    return $arr;
}

//2快速排序,分治递归
function quiSort($arr, $left = null, $right = null)
{
    $n = count($arr);
    $left = $left ?? 0;
    $right = $right ?? $n - 1;
    //递归条件
    if ($left < $right) {
        //升序降序核心
        $partIndex = getParIndex($arr, $left, $right);//获取中心基准，左右分区
        $arr = quiSort($arr, $left, $partIndex - 1);
        $arr = quiSort($arr, $partIndex + 1, $right);
    }
    return $arr;
}

//左右分区，并获取中心基准
function getParIndex(&$arr, $left, $right)
{
    $start = $left;
    $index = $start + 1;//记录位置
    for ($i = $index; $i <= $right; $i++) {
        //升序/降序(左大右小)
        if ($arr[$i] > $arr[$start]) {
            swp($arr, $i, $index);
            $index++;//下一位
        }
    }
    $result = $index - 1;//最终游标位置
    swp($arr, $start, $result);
    return $result;
}

//global $time;
$GLOBALS['time'] = 0;
$GLOBALS['time_rec'] = 0;
$GLOBALS['time_ref'] = 0;
//*3选择排序, 左占位,外层初始化最小值，比较剩余最小值
function selSort($arr)
{
    $n = count($arr);
    for ($i = 0; $i < $n; $i++) {
        $min = $arr[$i];//初始值
        $index = $i;
        for ($k = $i + 1; $k < $n; $k++) {
            //最小值
            if ($arr[$k] < $min) {
                $min = $arr[$k];
                $index = $k;
            }
        }
        swp($arr, $i, $index);
    }
    return $arr;

}

//4.1无限级树-递归，时间复杂度，层级， O(n^2)
function tree($arr, $pid = 0)
{
    //数据库数据生成树
    $tree = [];
    foreach ($arr as $item) {
        //递归条件，找到父节点
        if ($item['pid'] == $pid) {
            $children = tree($arr, $item['id']);//获取自身子节点
            if ($children) {
                $item['children'] = $children;
            }
            $tree[] = $item;//引用指针（地址值）
        }
    }
    return $tree;
}

function treeRec($arr)
{
    $time1 = microtime(true);
    $result = tree($arr);
    $time2 = microtime(true);
    $time = $time2 - $time1;
    var_dump("树递归时间{$time}");
//    return $time;
    return $result;
}

//4.2无限级树-引用法,时间复杂度，O(2n)
function treeRef($arr)
{
    $tree = [];
    //引用，记录对象的地址值
    $list = array_column($arr, null, 'id');
    foreach ($list as $k => $v) {
        //保证根节点在子节点前即可
        if (empty($list[$v['pid']])) {
            //一级节点
            $tree[] = &$list[$k];
        } else {
            //子节点
            $list[$v['pid']]['children'][] = &$list[$k];
        }
    }
    return $tree;
}


//$list = [1, 3, 11, 2, 9, 2, 0];
//$result = bubSort($list);
//$result = quiSort($list);
//$result = selSort($list);
//$data = getCatalogue();
//$result = treeRec($data);
//var_dump('引用法生成数');
//$result = treeRef($data);


//1.最大值，时间复杂度O（n）
function maxTest(array $list)
{
    if (count($list) < 1) {
        return 0;
    } else {
        $max = $list[0];
        foreach ($list as $item) {
            if ($item > $max) {
                $max = $item;
            }
        }
    }
    return $max;
}

function swapTest(&$arr, $i, $k)
{
    $tmp = $arr[$i];
    $arr[$i] = $arr[$k];
    $arr[$k] = $tmp;
}

//2.冒泡排序,升序
function bubbleSortTest(array $list)
{
    $n = count($list);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($k = 0; $k < $n - 1 - $i; $k++) {
            if ($list[$k] > $list[$k + 1]) {
                swapTest($list, $k, $k + 1);
            }
        }
    }
    return $list;
}


//3.快速排序,升序
function quickSortTest(array $list, $left = null, $right = null)
{
    $n = count($list);
    $left = $left ?? 0;//数组最左下标位置
    $right = $right ?? $n - 1;//数组最右下标位置
    //递归，分治
    if ($left < $right) {
        $partitionIndex = getPartition($list, $left, $right);//获取基准，并左右分区(注意引用)
        $list = quickSortTest($list, $left, $partitionIndex - 1);
        $list = quickSortTest($list, $partitionIndex + 1, $right);
    }
    return $list;
}

//左右分区并获得基准
function getPartition(&$list, $left, $right)
{
    $start = $left;
    $index = $start + 1;
    for ($i = $index; $i <= $right; $i++) {
        if ($list[$i] < $list[$start]) {
            swapTest($list, $i, $index);
            $index++;
        }
    }
    $result = $index - 1;//获取分区基准
    swapTest($list, $start, $result);//左右分区
    return $result;

}

//4.选择排序,升序
function selectSortTest(array $list)
{
    //左边最值占位
    $n = count($list);
    for ($i = 0; $i < $n - 1; $i++) {
        $min = $list[$i];
        $index = $i;
        for ($k = $i + 1; $k < $n; $k++) {
            if ($list[$k] < $min) {
                $min = $list[$k];//最小值
                $index = $k;//最小值下标
            }
        }
        swapTest($list, $i, $index);
    }
    return $list;
}

//5.1 无限级树递归法
function infiniteTreeRecursion(array $list, $pid = 0)
{
    $tree = [];
    foreach ($list as $item) {
        if ($item['pid'] == $pid) {
            $children = infiniteTreeRecursion($list, $item['id']);
            //叶子节点不加children
            if (!empty($children)) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }
    }
    return $tree;
}

//5.2 无限级树引用法
function infiniteTreeReference(array $list)
{
    $tree = [];
    $list = array_column($list, null, 'id');//以id作为索引
    foreach ($list as $k => $v) {
        if (empty($list[$v['pid']])) {
            //根节点
            $tree[] = &$list[$k];
        } else {
            //子节点
            $list[$v['pid']]['children'][] = &$list[$v['id']];
        }
    }
    return $tree;
}

$list = [3, 2, 1, 99, 12, 15, 16, 2];
//$result = maxTest($list);//1.最大值
//$result = bubbleSortTest($list);//2.冒泡排序
//$result = quickSortTest($list);//3.快速排序
//$result = selectSortTest($list);//4.选择排序
//6.插入排序,左边已排序数组，与当前值遍历比较，插入位置，右移
function insertSortTest(array $arr)
{
    $n = count($arr);
    //初始化有序数组
    for ($i = 1; $i < $n; $i++) {
        $preIndex = $i - 1;
        $current = $arr[$i];//升序，最大值
        //与有序数组，遍历比较插入位置
        while ($preIndex >= 0 && $arr[$preIndex] > $current) {
//            右移
            $arr[$preIndex + 1] = $arr[$preIndex];
            $preIndex--;//有序位置遍历位置移动
        }
        $arr[$preIndex + 1] = $current;//右移后最终替换位置，即插入位置

    }
    return $arr;
}

//7.希尔排序， 缩小增量排序，增量分组-插入排序。 插入排序的改进版
function shellSortTest(array $arr)
{

}


//$data = getCatalogue();
//$result = infiniteTreeRecursion($data);//5.1无限级树递归法
//$result = infiniteTreeReference($data);//5.2无限级树引用法
//$result = insertSortTest($list);

//$result = ceil(3.1);

var_dump($result);
die;






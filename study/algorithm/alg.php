<?php



//require_once __DIR__ . '/app/Helpers/functions.php';
require_once '../../app/Helpers/functions.php';
require_once './altree.php';

//ini_set("display_errors","Off");

define("FIBONACCI_NUM", 10);

const ASC = 1;
const DESC = 2;


//0.1 无限级树递归法
function treeRecursion(array $list, $pid = 0)
{
    $tree = [];
    foreach ($list as $item) {
        if ($item['pid'] == $pid) {
            $children = treeRecursion($list, $item['id']);
            //叶子节点不加children
            if (!empty($children)) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }
    }
    return $tree;
}

//0.2 无限级树引用法
function treeReference(array $list)
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

//0.3 无限级树的查找
function findTreeTarget($root, $target)
{
    $time = ++$GLOBALS['time'];
    var_dump("遍历第{$time}次");
    $result = -1;
    if (isset($root) && $root->val == $target) {
        return $root->index;
    } elseif ($root->left != null && $root->val < $target) {
        $result = findTreeTarget($root->right, $target);
    } elseif ($root->right != null && $root->val > $target) {
        $result = findTreeTarget($root->left, $target);
    }
    return $result;
}

//0.4 二叉树查找指定值
//$treeRoot = binaryTree();
//$target = 15;
//$index = findTreeTarget($treeRoot, $target);
//var_dump($index);


function catalogue()
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

//1.冒泡排序
//相邻交换，时间复杂度n^2，空间复杂度1
function bubbleSort(array $list, int $sort = ASC)
{
//    $time1 = getMicroTime();
    $count = count($list);
    //外层n-1,内层n-1-i
    for ($i = 0; $i < $count - 1; $i++) {
        for ($k = 0; $k < $count - 1 - $i; $k++) {
            if ($sort == ASC) {
                if ($list[$k] > $list[$k + 1]) {
                    swap($list[$k], $list[$k + 1]);
                }
            } elseif ($sort == DESC) {
                if ($list[$k] < $list[$k + 1]) {
                    swap($list[$k], $list[$k + 1]);
                }
            }

        }
    }
//    $time2 = getMicroTime();
//    var_dump($time1);
//    var_dump($time2);
//    $costTime = $time2 - $time1;
//    var_dump("冒泡排序时间:{$costTime}");
    return $list;
}

function swap(&$a, &$b)
{
    $tmp = $b;
    $b = $a;
    $a = $tmp;
}

//2.快速排序
//分治，递归,时间复杂度O(nlog2n)
function quickSort(array $array, int $left = null, int $right = null, $i = 0)
{
    $count = count($array);
    $left = $left ?? 0;
    $right = $right ?? $count - 1;
    if ($left < $right) {
        //1.进行交换分区并返回基准点，极限情况：2个返回：1或0
        $partitionIndex = partitionIndex($array, $left, $right);
        $i++;
//        var_dump("第{$i}次分区，index:{$partitionIndex}");
        //2.左边递归分区
        $array = quickSort($array, $left, $partitionIndex - 1, $i);
        //3.右边递归分区
        $array = quickSort($array, $partitionIndex + 1, $right, $i);
    }
    return $array;
}

//获取分区基准点
function partitionIndex(array &$arr, int $left, int $right)
{
    $pivot = $left;//初始基准点
    $index = $pivot + 1;//分区游标，指向小于初始基准末尾位的下一位
    for ($i = $index; $i <= $right; $i++) {
        //升序/降序（左大右小）
        if ($arr[$i] < $arr[$pivot]) {
            swap($arr[$i], $arr[$index]);
            $index++;//游标下一位
        }
    }
    //临界情况2个,直接交换
    swap($arr[$pivot], $arr[$index - 1]);//与当前游标指向的上一位互换，保证左小右大
    return $index - 1;
}

//3.选择排序,  从未排序中找到最值放到起始位置，之后每个位置按照相似方法获取最值
function selectSort(array $arr, int $type = ASC)
{
    $count = count($arr);
    for ($i = 0; $i < $count - 1; $i++) {
        $index = $i;//需要交换的最小值位置
        //获取未排序中最小值位置
        for ($j = $i + 1; $j < $count; $j++) {
            $index = getSelectIndexBySortType($arr, $j, $index, $type);
        }
        //最小值交换
        swap($arr[$index], $arr[$i]);
    }
    return $arr;


}

//3.1选择排序获取最值index， 升序/降序
function getSelectIndexBySortType(array $arr, int $i, int $index, int $type)
{
    if ($type == ASC) {
        if ($arr[$i] < $arr[$index]) {
            $index = $i;
        }
    } elseif ($type == DESC) {
        if ($arr[$i] > $arr[$index]) {
            $index = $i;
        }
    } else {
        exit("It's a wrong type!" . PHP_EOL);
    }
    return $index;
}

//4.插入排序, 【左边已排序数组，遍历比较插入位置，右移】
//时间复杂度，O(n^2)，空间复杂度O(1)
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

//5.希尔排序， 缩小增量排序，增量分组-插入排序。 插入排序的改进版
function shellSort(array $arr)
{
    $time1 = getMicroTime();
//    var_dump("希尔排序时间比较次数:");
    $time = 0;
    $n = count($arr);
    //增量确定，gap初始取一半值
    for ($gap = intval($n / 2); $gap > 0; $gap = intval($gap / 2)) {
        //从gap位置比较插入排序，gap最后缩小到1
        for ($i = $gap; $i < $n; $i++) {
//            $time++;
//            var_dump("比较第{$time}次");
            //插入排序
            $current = $arr[$i];//比较值，最大值
            $j = $i;//插入位置游标
            while ($j - $gap >= 0 && $current < $arr[$j - $gap]) {
//                $time += 2;
//                var_dump("比较移动第{$time}次");
                $arr[$j] = $arr[$j - $gap];//右移覆盖
                $j = $j - $gap;
            }
            $arr[$j] = $current;//插入替换位置
        }
    }
    $time2 = getMicroTime();
    var_dump($time1);
    var_dump($time2);
    $costTime = $time2 - $time1;
    var_dump("希尔排序时间:{$costTime}");
    return $arr;
}

//线性时间复杂度排序算法

//6.计数排序， 时间复杂度(n+k),空间复杂度(n+k)
//通过构造bucket数组，保存待排序值及次数，最后扫描最小值到最大值取出排序;
//局限性：如果最值相差大，取出时扫描次数为max-min+1
function countingSort(array $arr, int $min = null, int $max = null)
{
    $time1 = getMicroTime();
    $bucket = [];
    $array = [];
    $sortIndex = 0;//排序数字索引
    //获取数组最大值
    if (empty($max) && empty($min)) {
        //扫描一次获取最大值和最小值
        $max = $arr[0];
        $min = $arr[0];
        foreach ($arr as $item) {
            if ($item > $max) {
                $max = $item;
            }
            if ($item < $min) {
                $min = $item;
            }
        }
    }

    //生成bucket存储，以值为k，次数为value
    foreach ($arr as $item) {
        if (!isset($bucket[$item])) {
            $bucket[$item] = 0;//初始化值次数
        }
        $bucket[$item]++;
    }

    //通过bucket获取排序结果
    //从最小值扫描到最大值
    //如果最值相差非常大， 循环次数也非常大
    for ($k = $min; $k <= $max; $k++) {
        //存在重复项，循环取出
        while (isset($bucket[$k]) && $bucket[$k] > 0) {
            $array[$sortIndex++] = $k;
            $bucket[$k]--;
        }
    }
    $time2 = getMicroTime();
    var_dump($time1);
    var_dump($time2);
    $costTime = $time2 - $time1;
    var_dump("计数排序时间:{$costTime}");
    return $array;
}


/**
 * 7.桶排序，计数排序的改良,
 * 核心在于每个桶元素大小
 * 缺点：最值相差大，且数组个数少时，确定不好bucket_size，会导致bucket_count非常大，扫描效率降低
 * @param array $arr
 * @param int $bucketSize
 * @return array
 * @throws Exception
 */
function bucketSort(array $arr, int $bucketSize = 5)
{
//核心：桶大小，以及每个桶元素的大小
    $result = [];
    $bucket = [];
    $max = $arr[0];
    $min = $arr[0];
    foreach ($arr as $item) {
        if ($item > $max) {
            $max = $item;
        }
        if ($item < $min) {
            $min = $item;
        }
    }
    //核心：bucket_size的确定,bucket数量，由每个桶之间的间隔决定。
    //限制：桶数量必须小于数组长度
    $bucketCount = ($max - $min) / $bucketSize + 1;
    if ($bucketCount >= count($arr)) {
        throw new Exception("桶数量不小于数组长度，请重新确定bucket_size");
    }
    for ($i = 0; $i < $bucketCount; $i++) {
        $bucket[$i] = [];
    }

    foreach ($arr as $item) {
        $bucket[($item - $min) / $bucketSize][] = $item;
    }

    foreach ($bucket as $item) {
        $res = insertSort($item);
        foreach ($res as $re) {
            $result[] = $re;
        }
    }
    return $result;
}


class LinkNode
{
    public $val;
    public $next;
}

//生成单向循环链表
function generateLinkList($arr)
{
    $head = new LinkNode();
    $n = count($arr);
    if ($n <= 0) {
        return $head;
    }
    $first = $head;
    for ($i = 0; $i < $n - 1; $i++) {
        $nextNode = new LinkNode();
        $head->val = $arr[$i];
        $head->next = $nextNode;
        $head = $head->next;
    }
    //最后一个节点，指向首个节点
    $head->val = $arr[$n - 1];
    $head->next = $first;
    return $first;
}

/**
 * 遍历链表节点
 * @param LinkNode $head
 * @param int $count
 */
function getLinkNodes(LinkNode $head, int $count)
{
    $time = 0;
    while ($head != null && $time++ <= $count) {
        var_dump("当前节点" . PHP_EOL);
        var_dump($head->val);
        $head = $head->next;
    }

}

/**
 * 8.单向循环链表解决约瑟夫问题
 * 从第一个开始，数到第$num，该元素消失并从下一个重复开始，求最后$num-1个幸存者
 * @param LinkNode $head
 * @param int $total
 * @param int $num
 * @return LinkNode|null
 */
function josephQuestion(LinkNode $head, int $total, $num = 3)
{
    $left = $total;
    while ($head != null && $left >= $num) {
        //走num-2次，到第num-1个，再走1次，当第num个
        for ($i = 0; $i < $num - 2; $i++) {
            $head = $head->next;
        }
        $pre = $head;
        $head = $head->next;//当前节点释放，自杀
        //将上一个节点与下一个节点连接
        $pre->next = $head->next;
        var_dump("释放元素:{$head->val}");
        unset($head);
        $head = $pre->next;//下一个节点作为第一个节点
        $left--;
    }

    return $head;

}


//9.1二分查找,前提是有序数组
function binarySearch(array $arr, int $value)
{
    $len = count($arr);
    $left = 0;
    $right = $len - 1;
    $index = -1;
    while ($left <= $right) {
        //二分查找
        $mid = intval($right - $left / 2) + $left;
        if ($arr[$mid] == $value) {
            $index = $mid;
            break;
        } elseif ($arr[$mid] > $value) {
            //在左侧
            $right = $mid - 1;
        } else {
            //在右侧
            $left = $mid + 1;
        }
    }
    return $index >= 0 ? $index : false;
}

//9.2差值查找,二分查找的优化，前提是有序数组
function interpolationSearch(array $arr, int $value)
{
    $len = count($arr);
    $left = 0;
    $right = $len - 1;
    $result = -1;

    while ($left <= $right) {
        if ($left == $right) {
            //临界处理，只剩下一个值
            $mid = $left;
        } else {
            //核心：差值按比例获取最接近的数组下标
            $mid = $left + ($value - $arr[$left]) * ($right - $left) / ($arr[$right] - $arr[$left]);
            $mid = intval($mid);
        }
        //特殊值处理，不存在的值且小于数组最小值
        if ($mid < 0) {
            return $result;
        }
        //开始查找
        if ($arr[$mid] == $value) {
            return $mid;
        } elseif ($arr[$mid] > $value) {
            //左侧
            $right = $mid - 1;
        } else {
            //右侧
            $left = $mid + 1;
        }
    }
    return $result;
}

/**
 * 9.3获取斐波那契数列值
 * @param int $i
 * @return int
 * @throws Exception
 */
function fibonacciNum(int $i = FIBONACCI_NUM)
{
    if ($i < 1) {
        throw new Exception("fibonacciNum为大于0的整数");
    }

    //初始均取1
    if ($i == 1 || $i == 2) {
        return 1;
    }
    return fibonacciNum($i - 1) + fibonacciNum($i - 2);
}

/**
 * 9.4斐波那契查找有序数组，二分法的升级版
 * @param array $arr
 * @param int $val
 * @return int|mixed
 * @throws Exception
 */
function fibonacciSearch(array $arr, int $val)
{
    $len = count($arr);
    $left = 0;
    $right = $len - 1;
    $k = 1;
    $fibArr = [];//生成斐波那契数列
    //1.找到大于等于数组长度的最小值k，F(k)>=$len;
    try {
        $fibNum = fibonacciNum($k);
        $fibArr[$k - 1] = $fibNum;//初始化斐波那契数组
        while ($len > $fibNum) {
            $k++;
            $fibNum = fibonacciNum($k);
            $fibArr[$k - 1] = $fibNum;
        }
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
    //2.补全数组的长度至F(k),兼容刚好长度
    for ($i = $len - 1; $i < $fibNum; $i++) {
        $arr[$i] = $arr[$right];
    }
    $k--;//数组下标从0开始
    //3.进行黄金切割查找
    while ($left <= $right) {
        $mid = $left + $fibArr[$k - 1] - 1;//按照黄金分割比例查找
        if ($arr[$mid] == $val) {
            //找到,判断是否为补充元素
            if ($mid > $len - 1) {
                return $len - 1;
            } else {
                return $mid;
            }

        } elseif ($arr[$mid] > $val) {
            //左侧
            $right = $mid - 1;
            $k = $k - 1;//左分区，数组长度为F(k-1)
        } else {
            //右侧
            $left = $mid + 1;
            $k = $k - 2;//右分区，数组长度为F(k-2)
        }
    }

    return -1;
}


/**
 * 10. graph之关系网查找指定结果
 *  hashTable存储用户关系
 *  queue实现顺序查找，一度-二度-n度
 *  array存储已查询用户，避免重复死循环
 * @param array $me
 * @param string $res
 * @return mixed
 */
function graphSearch(array $me, $res = 'm')
{
    $result = [];
    $queue = [];
    $searched = [];
    $neighbours = $me['friends'];//朋友圈
    $neighbours[] = $me;//加上自己
    //初始化朋友圈
    pushQueue($queue, $neighbours);

    while (count($queue) > 0) {
        $item = array_pop($queue);
        //以查找过此人，跳过
        if (in_array($item['name'], $searched)) {
            continue;
        } else {
            if ($item['profession'] == $res) {
                unset($item['friends']);
                return $item;
            } elseif (!empty($friends = $item['friends'])) {
                //未找到，将其好友关系放入队列，查询结果记录
                $name = $item['name'];
                pushQueue($queue, $friends);
                array_push($searched, $name);
            }
        }
    }

    return $result;
}

/**
 * 10.1数组依次进队列
 * @param array $arr
 * @param array $push
 */

function pushQueue(array &$arr, array $push = null)
{
    foreach ($push as $item) {
        array_push($arr, $item);
    }
}


/**
 * 11.迪克斯特拉算法
 * 解决最短路径问题
 * 单向、无环、正权值图
 * @param array $costs
 * @param array $graph
 * @return array
 */
function dixtraAlgorithm(array $costs = [], array $graph = [])
{
    var_dump("开始查找最短路径");
    //1.图模型
    if (empty($graph)) {
        $graph = [
            'start'  => [
                'a' => 6,
                'b' => 2
            ],
            'a'      => [
                'finish' => 1,
            ],
            'b'      => [
                'a'      => 3,
                'finish' => 5
            ],
            'finish' => [

            ]
        ];
    }

    //2.节点开销，起始节点到当前节点的总开销
    if (empty($costs)) {
        $costs = [
            'a'      => 6,
            'b'      => 2,
            'finish' => INF//需要计算的总开销，初始化为无穷大
        ];
    }

    //3.父节点的散列表，用于获取最短路径
    $parents = [
        'a'      => 'start',
        'b'      => 'start',
        'finish' => null
    ];

    //4.已处理过的节点， 指基于当前节点的【所有邻居】节点开销都更新过
    $proceed = [];
    //5.获取未处理过的节点中找到最小开销节点
    $node = findLowestCostNode($costs, $proceed);

    while ($node != 'finish') {
        $cost = $costs[$node];//当前节点开销（起点到当前节点总开销）
        $neighbors = $graph[$node];//当前节点邻居

        //遍历当前节点的所有邻居
        foreach (array_keys($neighbors) as $key) {
            $newCost = $cost + $neighbors[$key];//当前节点开销+ 当前节点到邻居节点开销
            //节点最小开销比较
            if ($costs[$key] > $newCost) {
                $costs[$key] = $newCost;//更新到达key节点的最小开销
                $parents[$key] = $node;//更新该节点的父节点
            }

        }
        //当前节点标记为已处理
        array_push($proceed, $node);
        //下一个节点
        $node = findLowestCostNode($costs, $proceed);

    }

    //解析最短路径为数组
    return getShortPath($parents);
}

/**
 * 11.1解析最短路径
 * @param array $parents
 * @return array
 */
function getShortPath(array $parents)
{
    //construct tree from finish to start
    $parents = array_reverse($parents);
    $result = [];
    $tree = [];
    //改变子集结构为数组，方便扩展
    foreach ($parents as $k => $v) {
        unset($parents[$k]);
        $parents[$k]['pname'] = $v;
        $parents[$k]['name'] = $k;
    }

    $tree[] = &$parents['finish'];
    foreach ($parents as $k => $v) {
        $parents[$k]['parent'] = &$parents[$v['pname']];
    }

    $parent = $tree[0];
    while (!empty($parent)) {
        $result[] = $parent['name'];
        $parent = $parent['parent'];
    }
    $result[] = 'start';
    $result = array_reverse($result);

    return $result;
}

/**
 * 11.在未处理过的结点中，找到开销最小的结点
 * @param array $costs
 * @param array $processed
 * @return int|string|null
 */
function findLowestCostNode(array $costs, array $processed)
{
    $lowestCost = INF;//无穷大
    $lowestCostNode = null;//开销最小节点
    //遍历所有节点
    foreach ($costs as $key => $value) {
        $cost = $value;
        if ($cost < $lowestCost && !in_array($key, $processed)) {
            $lowestCost = $cost;
            $lowestCostNode = $key;
        }
    }

    return $lowestCostNode;
}


//11.迪克斯特拉算法，解决最短路径问题
$shortPath = dixtraAlgorithm();
var_dump($shortPath);




//10.查找关系网中指定职业人物，按照一度递增关系查找
//$mike = [
//    "name"       => "mike",
//    "friends"    => [],
//    "profession" => 'm',
//];
//$ellen = [
//    "name"       => "ellen",
//    "profession" => 'c',
//    "friends"    => []
//];
//$lily = [
//    "name"       => "lily",
//    "profession" => 'b',
//    "friends"    => [$bryan, $ellen, $mike]
//];
//$bryan = [
//    "name"       => 'bryan',
//    "profession" => 'a',
//    "friends"    => [$ellen, $lily]
//];
//$res = graphSearch($bryan, 'm');
//var_dump($res);


//$list = [1, 20, 1, 13, 5, 8, 9, 11, 0, 33, 99, 54, 0, 12321111, -1];
//$max = getMax($list);
//$bubble = bubbleSort($list);//1.冒泡算法
//$quick = quickSort($list);//2.快速算法
//$select = selectSort($list);//3.选择排序，最值tmp从最左开始取，和右边剩余数组比较取最小值并替换
//$insert = insertSort($list);//4.插入排序，已排序数组从左开始， 比较右移，插入替换
//$shell = shellSort($list);//5.希尔排序，简单插入排序改良版，时间复杂度O(n^1.5)
//$count = countingSort($list);//6.计数排序，通过构造bucket数组保存待排序值的次数，最后从最小值扫描到最大值进行排序
//$bucket = bucketSort($list,2000000);//7.桶排序，计数排序的改良版；bucket-size确定好，速度比count-sort快
//$result = $count;
//$result = $bucket;
//$list = [-1, 3, 4, 5];
//$linkNode = generateLinkList($list);
//getLinkNodes($linkList, count($list));
//$result = josephQuestion($linkNode, count($list), 2);//8.约瑟夫问题
//var_dump($result);




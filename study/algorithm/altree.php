<?php

class Node
{
    public $val;
    public $index;
    public $left = null;
    public $right = null;

    function __construct($value, $index)
    {
        $this->val = $value;
        $this->index = $index;
    }
}



/** 生成二叉树
 * @param array $arr
 * @return Node
 */
function binaryTree(array $arr = [])
{
//手动生成...
    $root = new Node(10, 0);
    $node1 = new Node(8, 1);
    $node2 = new Node(13, 2);
    $node3 = new Node(5, 3);
    $node4 = new Node(9, 4);
    $node5 = new Node(11, 5);
    $node6 = new Node(15, 6);

    $root->left = $node1;
    $root->right = $node2;

    $node1->left = $node3;
    $node1->right = $node4;

    $node2->left = $node5;
    $node2->right = $node6;
    //3层树
    return $root;

}


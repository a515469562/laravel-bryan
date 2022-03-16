<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
     CONST NO_PAY = 0;
     CONST PAID = 1;
     CONST DELIVERING = 2;
     CONST DELIVERED = 3;

     CONST STATUS_CN_MAP = [
         self::NO_PAY => '未支付',
         self::PAID => '已支付',
         self::DELIVERING => '发货中',
         self::DELIVERED => '已发货',
     ];

}

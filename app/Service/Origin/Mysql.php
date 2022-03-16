<?php


namespace App\Service\Origin;


use Exception;
use mysqli;

class Mysql
{

    private static $instance;
    private $connection;

    private $host;
    private $port;
    private $username;
    private $password;
    private $database;

    private $exType;
    const MYSQLI = 1;
    const PDO = 2;
    const TYPE_FUNC_MAP = [
        self::MYSQLI => 'mysqliQuery',
        self::PDO    => 'pdoQuery'
    ];


    /**
     * Mysql constructor.
     * @throws Exception
     */
    private function __construct()
    {
        $this->host = env('DB_HOST');
        $this->port = env('DB_PORT');
        $this->username = env('DB_USERNAME');
        $this->password = env('DB_PASSWORD');
        $this->database = env('DB_DATABASE');
        $this->exType = self::PDO;
//        $this->exType = self::MYSQLI;
        //连接实例化
        $res = $this->connectMysql();
        if ($res['status']) {
            $this->connection = $res['data'];
        }
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    //单例模式唯一入口
    public static function instance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new Mysql();
        }
        return self::$instance;
    }

    /**
     * 数据库连接，扩展mysqli
     * @return array
     * @throws Exception
     */
    public function connectMysql()
    {
        $exType = $this->exType;//mysql扩展类型
        try {
            switch ($exType) {
                case self::MYSQLI:
                    $connect = new mysqli($this->host, $this->username, $this->password, $this->database);
                    break;
                case self::PDO:
                    $dsn = "mysql:host={$this->host};dbname={$this->database}";
                    $connect = new \PDO($dsn, $this->username, $this->password);
                    break;
                default:
                    return ['status' => false, 'msg' => "{$exType}参数异常"];
                    break;
            }
        } catch (Exception $e) {
            $msg = "Mysql连接失败:{$e->getMessage()}";
            throw new Exception($msg);
        }
        return ['status' => true, 'data' => $connect];
    }

    /**
     * query查询并返回，注意sql注入
     * @param string $query
     * @return string
     * @throws Exception
     */
    public function query(string $query)
    {
        try {
            $func = self::TYPE_FUNC_MAP[$this->exType];
            $result = $this->$func($query);
        }catch (Exception $e){
            throw new Exception("mysql查询发生异常:{$e->getMessage()}");
        }
        return $result;

    }

    public function pdoQuery(string $query)
    {
        $result = [];
        $conn = $this->connection;
        foreach ($conn->query($query) as $row) {
            //剔除数字索引数据
            foreach ($row as $k => $v) {
                if (is_numeric($k)) {
                    unset($row[$k]);
                }
            }
            $result[] = $row;
        }
        return $result;
    }

    public function mysqliQuery(string $query)
    {
        $result = [];
        $con = $this->connection;
        if (isset($con)) {
            if ($con->real_query($query)) {
                if ($query = $con->store_result()) {
                    while ($row = $query->fetch_assoc()) {
                        $result[] = $row;
                    }
                }
            }
        }
        return $result;
    }
}

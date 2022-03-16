<?php


namespace App\Service\Origin;


class MysqlManager
{
    private $instance;

    public function __construct(Mysql $mysql)
    {
        $this->instance = $mysql;
    }

    /**
     * @param string $query
     * @return string
     * @throws \Exception
     */
    public function getQuery(string $query){
        return $this->instance->query($query);
    }

    public function getInstance(){
        return $this->instance;
    }
}

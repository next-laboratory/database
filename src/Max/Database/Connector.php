<?php
declare (strict_types=1);

namespace Max\Database;

use Exception;

class Connector
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * 连接池
     * @var array
     */
    protected $connectionPool = [];

    /**
     * @var mixed|string|null
     */
    protected $database = null;

    /**
     * Connector constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $this->database = $config['default'];
        if (!isset($config[$this->database])) {
            throw new Exception('没有找到' . $this->database . '相关的配置');
        }
        $config = $config[$this->database];
        $dsn    = $this->dsn($config);
        //TODO 优化
        $this->connect($dsn, $config);
    }

    /**
     * @param array $config
     * @return mixed|string
     */
    public function dsn(array $config)
    {
        if (!isset($config['dsn']) || empty($dsn = $config['dsn'])) {
            $dsn = "{$this->database}:host={$config['host']};port={$config['port']};dbname={$config['dbname']};";
        }
        return $dsn;
    }

    /**
     * 数据库连接方法
     * @throws /PDOException
     */
    protected function connect($dsn, $config)
    {
        $this->pdo = new \PDO(
            $dsn,
            $config['user'],
            $config['pass'],
            $config['options']
        );
    }

    public function connectionPool()
    {

    }

    /**
     * PDO实例
     * @return mixed
     */
    public function getPdo(bool $isRead = true)
    {
        return $this->pdo;
    }

    public function statement($query, $bindings = [])
    {
        $statement = $this->getPdo()->prepare($query);

        $this->bindValues($statement, $bindings);

        $statement->execute();

        return $statement;
    }

    public function bindValues(\PDOStatement $PDOStatement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $PDOStatement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

}

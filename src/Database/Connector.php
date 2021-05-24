<?php
declare(strict_types=1);

namespace Max\Database;

use Max\Foundation\App;

class Connector
{

//    const FETCHTYPE = \PDO::FETCH_ASSOC;

//    private $PDOstatement;

    protected $pdo;

    protected $database = null;

    public function __construct(App $app)
    {
        $config         = $app->config->get('database');
        $this->database = $config['default'] ?? 'mysql';
        $config         = $config[$this->database];
        $dsn            = $this->dsn($config);
        //TODO 优化
        $this->connect($dsn, $config);
    }

    public function dsn(array $config)
    {
        if (!isset($config['dsn']) || empty($dsn = $config['dsn'])) {
            $dsn = $this->database . ':';
            $dsn .= "host={$config['host']};";
            $dsn .= "port={$config['port']};";
            $dsn .= "dbname={$config['dbname']};";
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

    /**
     * PDO实例
     * @return mixed
     */
    public function handle()
    {
        return $this->pdo;
    }

}
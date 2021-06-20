<?php
declare(strict_types=1);

namespace Max\Database;

use Max\App;

class Connector
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var mixed|string|null
     */
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
    public function handle($type = null)
    {
        return $this->pdo;
    }

}
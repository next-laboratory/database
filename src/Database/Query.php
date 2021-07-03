<?php
declare(strict_types=1);

namespace Max\Database;

use Max\App;

/**
 * 数据库外部接口
 * @method $this name(string $table_name, string $alias = '') 表名设置方法, 不带前缀
 * @method $this table(string $table_name, string $alias = '') 表名设置方法，带前缀
 * @method $this where(array $where, string $operator = '=')
 * @method $this whereLike(array $whereLike)
 * @method $this whereNull($whereNull)
 * @method $this whereNotNull($whereNotNull)
 * @method $this whereOr(array $whereOr, string $operator = '=')
 * @method $this whereIn(array $whereIn = [])
 * @method $this whereNotIn($whereNotIn)
 * @method $this whereBetween(array $whereBetween)
 * @method $this whereExists(array $whereExists)
 * @method $this order(array $order)
 * @method $this join(array $joinTables)
 * @method $this leftJoin(array $joinTables)
 * @method $this rightJoin(array $joinTables)
 * @method $this fields($fields)
 * @method $this group(array $group)
 * @method $this limit(int $limit, int $offset = null)
 * Class Db
 * @package Max
 */
class Query
{

    /**
     * 容器实例
     * @var App
     */
    protected $app;

    /**
     * 数据库类型
     * @var string
     */
    protected $database = '';

    /**
     * true可以打印SQL和绑定的变量
     * @var bool
     */
    protected $debug = false;

    /**
     * SQL构造器类名
     * @var string
     */
    protected $builderClass = '';

    /**
     * SQL构造器实例
     * @var Builder
     */
    protected $builder;

    /**
     * true 实例将不会保存到容器中
     * @var bool
     */
//     public static $__refreshable = true;
    /**
     * 历史SQL
     * @var array
     */
    protected $history = [];

    /**
     * 数据库驱动
     * @var string
     */
    const NAMESPACE = '\\Max\\Database\\Builder\\';

    /**
     * 初始化实例列表和配置
     * Query constructor.
     * @param App $app
     * @throws \Exception
     */
    public function __construct(App $app)
    {
        $this->app          = $app;
        $this->database     = $app->config->get('database.default');
        $this->builderClass = static::NAMESPACE . ucfirst($this->database);
        $this->builder      = new $this->builderClass;
    }

    /**
     * 测试当前查询
     * @return $this
     */
    public function getSQL(): Query
    {
        $this->debug = true;
        return $this;
    }

    /**
     * PDO实例
     * @param string $type
     * @return mixed
     */
    public function PDO(string $type = 'read')
    {
        return $this->app->make(Connector::class)->handle($type);
    }

    /**
     * 实际调用驱动方法的方法
     * @param $method
     * @param $args
     * @return $this
     */
    public function __call($method, $args)
    {
        $this->builder->{$method}(...$args);
        return $this;
    }

    /**
     * 查询
     * @param string $query
     * @param array $bindParams
     * @param bool $all
     * @return mixed
     */
    public function query(string $query, array $bindParams = [], bool $all = true)
    {
        $fetch = $all ? 'fetchAll' : 'fetch';
        return $this->execute($query, $bindParams)->{$fetch}(\PDO::FETCH_ASSOC);
    }

    /**
     * 执行一条SQL
     * @param string $query
     * @param array $bindParams
     * @return int
     */
    final public function exec(string $query, array $bindParams = []): int
    {
        return $this->execute($query, $bindParams)->rowCount();
    }

    /**
     * 查询
     * @return Collection
     */
    public function select(): Collection
    {
        $query      = $this->builder->select();
        $bindParams = $this->builder->getBindParams();
        return new Collection(function (Collection $collection) use ($query, $bindParams) {
            return $this->fetchAll($query, $bindParams, \PDO::FETCH_ASSOC);
        }, $query, $bindParams);
    }

    /**
     * 获取某一个值的查询
     * @param $field
     * @return |null
     */
    public function value($field)
    {
        return $this->fetch($this->builder->select($field), $this->builder->getBindParams());
    }

    /**
     * 查询一列
     * @param $column
     * 字段名
     * @return array
     */
    public function column($column): array
    {
        return $this->fetchAll($this->builder->select($column), $this->builder->getBindParams(), \PDO::FETCH_COLUMN);
    }

    /**
     * 获取单条
     * @param string $query
     * @param array $binds
     * @param int $fetchType
     * @return mixed
     */
    public function fetch(string $query, array $binds = [], int $fetchType = \PDO::FETCH_COLUMN)
    {
        return $this->execute($query, $binds)->fetch($fetchType);
    }

    /**
     * 获取全部
     * @param string $query
     * @param array $binds
     * @param int $fetchType
     * @return array
     */
    public function fetchAll(string $query, array $binds = [], int $fetchType = \PDO::FETCH_COLUMN)
    {
        return $this->execute($query, $binds)->fetchAll($fetchType);
    }

    /**
     * 查询总数
     * @param int|string $field
     * count的字段
     * @return int
     * @throws \Exception
     */
    public function count($field = '*'): int
    {
        return (int)$this->fetch($this->builder->select("COUNT(${field})"), $this->builder->getBindParams());
    }

    /**
     * 求和
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function sum($field): int
    {
        return (int)$this->fetch($this->builder->select("SUM({$field})"), $this->builder->getBindParams());
    }

    /**
     * 查询字段最大值
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function max($field): int
    {
        return (int)$this->fetch($this->builder->select("MAX({$field})"), $this->builder->getBindParams());
    }

    /**
     * 查询字段最小值
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function min($field): int
    {
        return (int)$this->fetch($this->builder->select("MIN({$field})"), $this->builder->getBindParams());
    }

    /**
     * 查询字段平均值
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function avg($field): int
    {
        return (int)$this->fetch($this->builder->select("AVG({$field})"), $this->builder->getBindParams());
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        $query      = $this->builder->select();
        $bindParams = $this->builder->getBindParams();
        return new Collection(function (Collection $collection) use ($query, $bindParams) {
            return $this->fetch($query, $bindParams, \PDO::FETCH_ASSOC);
        }, $query, $bindParams);
    }

    /**
     * 更新
     * @param array $data
     * @return int
     */
    public function update(array $data): int
    {
        return $this->execute($this->builder->update($data))
            ->rowCount();
    }

    /**
     * 插入
     * @param array $data
     * @return string
     */
    public function insert(array $data): string
    {
        $this->execute($this->builder->insert($data));
        return $this->PDO()->lastinsertid();
    }

    /**
     * 删除数据
     * @return mixed
     */
    public function delete()
    {
        return $this->execute($this->builder->delete())
            ->rowCount();
    }

    /**
     * 开启事务
     */
    public function begin()
    {
        $this->autoCommit(false);
        $this->PDO()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->PDO()->commit();
        $this->autoCommit();
    }

    /**
     * 自动提交事务状态更改
     * @param bool $autoCommit
     */
    public function autoCommit(bool $autoCommit = true)
    {
        $this->PDO()->setAttribute(\PDO::ATTR_AUTOCOMMIT, $autoCommit);
    }

    /***
     * 回滚事务
     */
    public function rollback()
    {
        $this->PDO()->rollBack();
        $this->autoCommit();
    }

    /**
     * 事务
     * @param \Closure $transaction
     * 需要执行的事务，返回执行结果,有两个参数，一个为query实例，第二个为pdo实例
     * @return mixed
     * @throws \Exception
     */
    public function transaction(\Closure $transaction)
    {
        $pdo = $this->PDO();
        $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);
        try {
            $pdo->beginTransaction();
            $result = $transaction($this, $pdo);
            $pdo->commit();
            return $result;
        } catch (\PDOException $e) {
            $pdo->rollback();
            throw $e;
        } finally {
            $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
        }
    }

    /**
     * 执行SQL
     * @param string $query
     * @param array $data
     * @return \PDOStatement
     */
    protected function execute(string $query, array $bindParams = null): \PDOStatement
    {
        $bindParams = $bindParams ?? $this->builder->getBindParams();
        try {
            $queryString = sprintf(str_replace('?', '%s', $query), ...array_map(function ($value) {
                return is_string($value) ? "'{$value}'" : (string)$value;
            }, $bindParams));
        } catch (\Exception $e) {
            $queryString = $query;
        }

        if ($this->debug) {
            halt($queryString);
        }
        $startTime          = microtime(true);
        $this->PDOstatement = $this->PDO()->prepare($query);
        $this->PDOstatement->execute($bindParams);
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $slowLog  = $this->app->config->get('database.slow_log');
        if (false !== $slowLog && $duration >= $slowLog) {
            $this->app['log']->debug("{$queryString}", ['Time' => $duration . 'ms', 'SQL' => $query]);
        }
        $this->history[] = [$queryString, $duration];
        $this->builder   = new $this->builderClass;
        return $this->PDOstatement;
    }


    /**
     * 历史SQL取得
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

}

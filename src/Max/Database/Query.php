<?php
declare(strict_types=1);

namespace Max\Database;

/**
 * 数据库外部接口
 * @method $this name(string $table_name, string $alias = '') 表名设置方法, 不带前缀
 * @method $this where(array $where, string $operator = '=')
 * @method $this whereLike(array $whereLike)
 * @method $this whereNull($whereNull)
 * @method $this whereNotNull($whereNotNull)
 * @method $this whereOr(array $whereOr, string $operator = '=')
 * @method $this whereIn(array $whereIn = [])
 * @method $this whereNotIn($whereNotIn)
 * @method $this whereBetween(array $whereBetween)
 * @method $this whereExists(array $whereExists)
 * @method $this order(string $field, $sort = 'ASC')
 * @method $this join(string $table, string $on = '', string $type = 'INNER')
 * @method $this leftJoin(string $table, string $on)
 * @method $this rightJoin(string $table, string $on)
 * @method $this crossJoin(string $table)
 * @method $this fields($fields)
 * @method $this group(string $groupBy, $having = '')
 * @method $this limit(int $limit, int $offset = null)
 * Class Db
 * @package Max
 */
class Query
{

    /**
     * 历史记录
     * @var History
     */
    protected $history;

    /**
     * SQL构造器类名
     * @var string
     */
    protected $builderClass = '';

    /**
     * SQL构造器实例
     * @var AbstractBuilder
     */
    protected $builder;

    /**
     * 监听函数
     * @var \Closure
     */
    protected $listener;

    /**
     * @var Connector
     */
    protected $connector;

    protected $primaryKey = 'id';

    /**
     * 驱动基础命名空间
     * @var string
     */
    protected const NAMESPACE = '\\Max\\Database\\Builder\\';

    protected $model;

    /**
     * Query constructor.
     * @param array $config
     * @throws \Exception
     */
    public function __construct()
    {
        $config             = config('database');
        $this->builderClass = static::NAMESPACE . ucfirst($config['default']);
        $this->builder      = new $this->builderClass;
        $this->history      = new History();
        $this->connector    = new Connector($config);
    }

    public static function __setter(\Max\App $app)
    {
        return new static($app->config->get('database'));
    }

    public function find($id, array $columns = [])
    {
        $statement = $this->connector->statement($this->builder->where([$this->primaryKey => $id])->select(), $this->builder->getBindParams());
        $record    = $statement->fetch(\PDO::FETCH_ASSOC);
        return isset($this->model) ? new ($this->model)($record) : $record;
    }

    public function setPrimaryKey(string $key)
    {
        $this->primaryKey = $key;
        return $this;
    }

    public function connection(bool $isRead = true)
    {
        return $this->connector->getPdo($isRead);
    }

    public function setTable(string $table, $alias = null)
    {
        $this->builder->table($table, $alias);
        return $this;
    }

    public static function name(string $table, $alias = null)
    {
        return static::table($table, $alias);
    }

    public static function table(string $table, $alias = null)
    {
        return (new static())->setTable($table, $alias);
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

    public function setModel(string $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * 查询
     * @return Collection
     */
    public function select($field = null): Collection
    {
        $statement = $this->connector->statement($this->builder->select($field), $this->builder->getBindParams());
        $records   = new Collection();
        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $records->add(isset($this->model) ? new ($this->model)($record) : $record);
        }

        return $records;
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
        return $this->execute($query, $binds, true)->fetchAll($fetchType);
    }

    /**
     * 查询总数
     * @param string $field
     * @return int
     * @throws \Exception
     */
    public function count($field = '*'): int
    {
        return $this->aggregate("COUNT({$field})");
    }

    /**
     * 求和
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function sum($field): int
    {
        return $this->aggregate("SUM($field)");
    }

    /**
     * 查询字段最大值
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function max($field): int
    {
        return $this->aggregate("MAX({$field})");
    }

    /**
     * 查询字段最小值
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function min($field): int
    {
        return $this->aggregate("MIN({$field})");
    }

    /**
     * 查询字段平均值
     * @param $field
     * @return int
     * @throws \Exception
     */
    public function avg($field): int
    {
        return $this->aggregate("AVG({$field})");
    }

    /**
     * @param $expression
     * @return int
     * @throws \Exception
     */
    public function aggregate($expression): int
    {
        return (int)$this->fetch($this->builder->select($expression), $this->builder->getBindParams());
    }

    /**
     * @return Collection
     */
    public function get(array $columns = ['*'])
    {
        $statement = $this->connector->statement($this->builder->select(), $this->builder->getBindParams());
        $record    = $statement->fetch(\PDO::FETCH_ASSOC);
        return isset($this->model) ? new ($this->model)($record) : $record;
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
        return $this->connection()->lastinsertid();
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
        $this->connection()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->connection()->commit();
        $this->autoCommit();
    }

    /**
     * 自动提交事务状态更改
     * @param bool $autoCommit
     */
    public function autoCommit(bool $autoCommit = true)
    {
        $this->connection()->setAttribute(\PDO::ATTR_AUTOCOMMIT, $autoCommit);
    }

    /***
     * 回滚事务
     */
    public function rollback()
    {
        $this->connection()->rollBack();
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
        $pdo = $this->connection();
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
    public function execute(string $query, array $bindParams = null, bool $isRead = true): \PDOStatement
    {
        $bindParams = $bindParams ?? $this->builder->getBindParams();
        $startTime  = microtime(true);
        try {
            $this->PDOstatement = $this->connection($isRead)->prepare($query);
            $this->PDOstatement->execute($bindParams);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage() . "(SQL: $query)");
        } finally {
            $this->trigger($query, $bindParams);
        }
        $time = round((microtime(true) - $startTime) * 1000, 4);
        $this->history->push(['query' => $query, 'time' => $time, 'bindParams' => $bindParams]);
        $this->builder = new $this->builderClass;
        return $this->PDOstatement;
    }

    /**
     * 历史SQL取得
     * @return array
     */
    public function getHistory(): \IteratorAggregate
    {
        return $this->history;
    }

    public function listen(\Closure $handle)
    {
        $this->listener = $handle;
    }

    public function trigger(...$arguments)
    {
        if (!is_null($this->listener)) {
            return ($this->listener)(...$arguments);
        }
    }

}

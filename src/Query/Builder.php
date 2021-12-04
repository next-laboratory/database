<?php

namespace Max\Database\Query;

use Max\Database\Collection;
use Max\Database\Connector;
use Max\Database\Contracts\ConnectorInterface;
use Max\Database\Contracts\GrammarInterface;

class Builder
{
    /**
     * @var array|null
     */
    public ?array $where;

    /**
     * @var array
     */
    public array $select;

    /**
     * @var array
     */
    public array $from;

    /**
     * @var array
     */
    public array $order;

    /**
     * @var array
     */
    public array $group;

    /**
     * @var array
     */
    public array $having;

    /**
     * @var array
     */
    public array $join;

    /**
     * @var array
     */
    public int $limit;

    /**
     * @var int
     */
    public int $offset;

    /**
     * @var array
     */
    public array $bindings = [];

    /**
     * @var GrammarInterface
     */
    protected GrammarInterface $grammar;

    /**
     * @var ConnectorInterface
     */
    protected ConnectorInterface $connector;

    /**
     * @param ConnectorInterface $connector
     * @param GrammarInterface   $grammar
     */
    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
        $this->grammar   = $connector->getGrammar();
    }

    /**
     * @param string $connection
     */
    public function setConnection(string $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     * @param null   $alias
     *
     * @return $this
     */
    public function from(string $table, $alias = null)
    {
        $this->from = func_get_args();

        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param null   $value
     *
     * @return $this
     */
    public function where(string $column, string $operator, $value = null)
    {
        $where = [$column, $operator];

        if (!is_null($value)) {
            $where[] = '?';
            $this->addBindings($value);
        }
        $this->where[] = $where;

        return $this;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function whereNull(string $column)
    {
        return $this->where($column, 'IS NULL');
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function whereNotNull(string $column)
    {
        return $this->where($column, 'IS NOT NULL');
    }

    /**
     * @param $column
     * @param $value
     *
     * @return $this
     */
    public function whereLike($column, $value)
    {
        return $this->where($column, 'LIKE', $value);
    }

    /**
     * @param string $column
     * @param array  $in
     *
     * @return $this
     */
    public function whereIn(string $column, array $in)
    {
        if (empty($in)) {
            return $this;
        }
        $this->addBindings($in);
        $this->where($column, sprintf('IN (%s)', rtrim(str_repeat('?, ', count($in)), ' ,')));

        return $this;
    }

    public function whereRaw(string $expression, array $bindings = [])
    {
        $this->where[] = new Expression($expression);
        $this->setBindings($bindings);

        return $this;
    }

    /**
     * @param        $table
     * @param        $alias
     * @param string $league
     *
     * @return Join
     */
    public function join($table, ?string $alias = null, $league = 'INNER JOIN')
    {
        return $this->join[] = new Join($this, $table, $alias, $league);
    }

    /**
     * @param $table
     * @param $alias
     *
     * @return Join
     */
    public function leftJoin($table, ?string $alias = null)
    {
        return $this->join($table, $alias, 'LEFT OUTER JOIN');
    }

    public function rightJoin($table, ?string $alias = null)
    {
        return $this->join($table, $alias, 'RIGHT OUTER JOIN');
    }

    public function whereBetween($column, $start, $end)
    {
        $this->addBindings([$start, $end]);

        return $this->where($column, 'BETWEEN(? and ?)');
    }

    protected function addBindings($value)
    {
        if (is_array($value)) {
            array_push($this->bindings, ...$value);
        } else {
            $this->bindings[] = $value;
        }
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function setBindings($bindings)
    {
        if (is_array($bindings)) {
            $this->bindings = [...$this->bindings, ...$bindings];
        } else {
            $this->bindings[] = $bindings;
        }
    }

    public function select(array $columns = ['*'])
    {
        $this->select = $columns;

        return $this;
    }

    public function order($column, $order = '')
    {
        $this->order[] = func_get_args();

        return $this;
    }

    public function group($column)
    {
        $this->group[] = $column;

        return $this;
    }

    public function having($first, $operator, $last)
    {
        $this->having[] = func_get_args();

        return $this;
    }

    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function toSql($columns = ['*']): string
    {
        if (empty($this->select)) {
            $this->select($columns);
        } else {
            if (['*'] === $columns) {
                $this->select();
            } else {
                $this->select(array_merge($this->select, $columns));
            }
        }

        return $this->grammar->generateSelectQuery($this);
    }

    public function get(array $columns = ['*'])
    {
        return Collection::make(
            $this->run($this->toSql($columns))
                 ->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    public function count($column = '*'): int
    {
        return $this->aggregate("COUNT({$column})");
    }

    public function sum($column): int
    {
        return $this->aggregate("SUM($column)");
    }

    public function max($column): int
    {
        return $this->aggregate("MAX({$column})");
    }

    public function min($column): int
    {
        return $this->aggregate("MIN({$column})");
    }

    public function avg($column): int
    {
        return $this->aggregate("AVG({$column})");
    }

    protected function aggregate(string $expression): int
    {
        return (int)$this->run($this->toSql((array)($expression . ' AS AGGREGATE ')))
                         ->fetchColumn(0);
    }

    /**
     * 事务
     *
     * @param \Closure $transaction
     *
     * @return mixed
     */
    public function transaction(\Closure $transaction)
    {
        $PDO = $this->connection->getPDO();
        try {
            $PDO->beginTransaction();
            $result = $transaction($this, $PDO);
            $PDO->commit();
            return $result;
        } catch (\PDOException $e) {
            $PDO->rollback();
            throw $e;
        }
    }

    public function exists(): bool
    {
        $query = sprintf('SELECT EXISTS(%s) AS MAX_EXIST', $this->toSql());

        return (bool)$this->run($query)->fetchColumn(0);
    }

    public function column(string $column, ?string $key = null)
    {
       $result = $this->run($this->toSql(array_filter([$column, $key])))->fetchAll();

        return Collection::make($result ?: [])->pluck($column, $key);
    }

    public function find($id, array $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    public function first(array $columns = ['*'])
    {
        return $this->run($this->toSql($columns))->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete()
    {
        return $this->run($this->grammar->generateDeleteQuery($this))->rowCount();
    }

    public function insert(array $data)
    {
        $this->column   = array_keys($data);
        $this->bindings = array_values($data);
        $this->run($this->grammar->generateInsertQuery($this));

        return $this->connector->getPDO()->lastInsertId();
    }

    public function insertAll(array $data)
    {
        return array_map(function($item) {
            return $this->insert($item);
        }, $data);
    }

    public function update(array $data)
    {
        $query = $this->grammar->generateUpdateQuery($this, $data);

        return $this->run($query)->rowCount();
    }

    public function run(string $query): \PDOStatement
    {
        $PDOStatement = $this->connector->statement($query, $this->bindings);

        $PDOStatement->execute();

        return $PDOStatement;
    }

}

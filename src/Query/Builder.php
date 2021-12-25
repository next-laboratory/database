<?php

namespace Max\Database\Query;

use Max\Database\Collection;
use Max\Database\Connector;
use Max\Database\Contracts\ConnectorInterface;
use Max\Database\Contracts\GrammarInterface;

class Builder
{
    /**
     * @var array|string[]
     */
    protected static array $clause = [
        'aggregate', 'select', 'from', 'join', 'where',
        'group', 'having', 'order', 'limit', 'offset', 'lock'
    ];

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
     * @var ConnectorInterface
     */
    protected ConnectorInterface $connector;

    /**
     * @param ConnectorInterface $connector
     */
    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
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
     * @param        $value
     * @param string $operator
     *
     * @return $this
     */
    public function where(string $column, $value, string $operator = '=')
    {
        $this->where[] = [$column, $operator, '?'];
        $this->addBindings($value);

        return $this;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function whereNull(string $column)
    {
        $this->where[] = [$column, 'IS NULL'];

        return $this;
    }

    /**
     * @param string $column
     *
     * @return $this
     */
    public function whereNotNull(string $column)
    {
        $this->where[] = [$column, 'IS NULL'];

        return $this;
    }

    /**
     * @param $column
     * @param $value
     *
     * @return $this
     */
    public function whereLike($column, $value)
    {
        return $this->where($column, $value, 'LIKE');
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
        $this->where($column, sprintf('(%s)', rtrim(str_repeat('?, ', count($in)), ' ,')), 'IN');

        return $this;
    }

    /**
     * @param string $expression
     * @param array  $bindings
     *
     * @return $this
     */
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

    /**
     * @param             $table
     * @param string|null $alias
     *
     * @return Join
     */
    public function rightJoin($table, ?string $alias = null)
    {
        return $this->join($table, $alias, 'RIGHT OUTER JOIN');
    }

    /**
     * @param $column
     * @param $start
     * @param $end
     *
     * @return $this
     */
    public function whereBetween($column, $start, $end)
    {
        $this->addBindings([$start, $end]);

        return $this->where($column, '(? AND ?)', 'BETWEEN');
    }

    /**
     * @param $value
     *
     * @return void
     */
    protected function addBindings($value)
    {
        if (is_array($value)) {
            array_push($this->bindings, ...$value);
        } else {
            $this->bindings[] = $value;
        }
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @param $bindings
     *
     * @return void
     */
    public function setBindings($bindings)
    {
        if (is_array($bindings)) {
            $this->bindings = [...$this->bindings, ...$bindings];
        } else {
            $this->bindings[] = $bindings;
        }
    }

    /**
     * @param array $columns
     *
     * @return $this
     */
    public function select(array $columns = ['*'])
    {
        $this->select = $columns;

        return $this;
    }

    /**
     * @param $column
     * @param $order
     *
     * @return $this
     */
    public function order($column, $order = '')
    {
        $this->order[] = func_get_args();

        return $this;
    }

    /**
     * @param $column
     *
     * @return $this
     */
    public function group($column)
    {
        $this->group[] = $column;

        return $this;
    }

    /**
     * @param $first
     * @param $operator
     * @param $last
     *
     * @return $this
     */
    public function having($first, $operator, $last)
    {
        $this->having[] = func_get_args();

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param $columns
     *
     * @return string
     */
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

        return $this->generateSelectQuery();
    }

    /**
     * @param array $columns
     *
     * @return Collection
     */
    public function get(array $columns = ['*'])
    {
        return Collection::make($this->connector->run(
            $this->toSql($columns),
            $this->bindings
        )->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function count($column = '*'): int
    {
        return $this->aggregate("COUNT({$column})");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function sum($column): int
    {
        return $this->aggregate("SUM($column)");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function max($column): int
    {
        return $this->aggregate("MAX({$column})");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function min($column): int
    {
        return $this->aggregate("MIN({$column})");
    }

    /**
     * @param $column
     *
     * @return int
     */
    public function avg($column): int
    {
        return $this->aggregate("AVG({$column})");
    }

    /**
     * @param string $expression
     *
     * @return int
     */
    protected function aggregate(string $expression): int
    {
        return (int)$this->connector->run(
            $this->toSql((array)($expression . ' AS AGGREGATE ')),
            $this->bindings
        )->fetchColumn(0);
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

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return (bool)$this->connector->run(
            sprintf('SELECT EXISTS(%s) AS MAX_EXIST', $this->toSql()),
            $this->bindings
        )->fetchColumn(0);
    }

    /**
     * @param string      $column
     * @param string|null $key
     *
     * @return Collection
     */
    public function column(string $column, ?string $key = null)
    {
        $result = $this->connector->run($this->toSql(array_filter([$column, $key])), $this->bindings)->fetchAll();

        return Collection::make($result ?: [])->pluck($column, $key);
    }

    /**
     * @param        $id
     * @param array  $columns
     * @param string $identifier
     *
     * @return mixed
     */
    public function find($id, array $columns = ['*'], string $identifier = 'id')
    {
        return $this->where($identifier, $id)->first($columns);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function first(array $columns = ['*'])
    {
        return $this->connector->run($this->toSql($columns), $this->bindings)->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @return int
     */
    public function delete()
    {
        return $this->connector->run($this->generateDeleteQuery(), $this->bindings)->rowCount();
    }

    /**
     * @param array $data
     *
     * @return false|string
     */
    public function insert(array $data)
    {
        $this->column = array_keys($data);
        $this->connector->run($this->generateInsertQuery(), $this->bindings = array_values($data));

        return $this->connector->getPDO()->lastInsertId();
    }

    /**
     * @param array $data
     *
     * @return array|false[]|string[]
     */
    public function insertAll(array $data)
    {
        return array_map(function($item) {
            return $this->insert($item);
        }, $data);
    }

    /**
     * @param array $data
     *
     * @return int
     */
    public function update(array $data)
    {
        $query = $this->generateUpdateQuery($data);

        return $this->connector->run($query, $this->bindings)->rowCount();
    }

    /**
     * @return string
     */
    protected function compileJoin()
    {
        $joins = array_map(function(Join $item) {
            $alias = $item->alias ? 'AS ' . $item->alias : '';
            $on    = $item->on ? ('ON ' . implode(' ', $item->on)) : '';
            return ' ' . $item->league . ' ' . $item->table . ' ' . $alias . ' ' . $on;
        }, $this->join);

        return implode('', $joins);
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileWhere()
    {
        $whereCondition = [];
        foreach ($this->where as $where) {
            $whereCondition[] = $where instanceof Expression ? $where->__toString() : implode(' ', $where);
        }
        return ' WHERE ' . implode(' AND ', $whereCondition);
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileFrom()
    {
        return ' FROM ' . implode(' AS ', array_filter($this->from));
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileSelect()
    {
        return implode(', ', $this->select);
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileLimit()
    {
        return ' LIMIT ' . $this->limit;
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileOffset()
    {
        return ' OFFSET ' . $this->offset;
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileOrder()
    {
        $orderBy = array_map(function($item) {
            return $item[0] instanceof Expression ? $item[0]->__toString() : implode(' ', $item);
        }, $this->order);

        return ' ORDER BY ' . implode(', ', $orderBy);
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileGroup()
    {
        return ' GROUP BY ' . implode(', ', $this->group);
    }

    /**
     * @param
     *
     * @return string
     */
    protected function compileHaving()
    {
        $having = array_map(function($item) {
            return implode(' ', $item);
        }, $this->having);

        return ' HAVING ' . implode(' AND ', $having);
    }

    /**
     * @param
     *
     * @return string
     */
    public function generateSelectQuery()
    {
        $query = 'SELECT ';
        foreach (static::$clause as $value) {
            $compiler = 'compile' . ucfirst($value);
            if (!empty($this->{$value})) {
                $query .= $this->{$compiler}($this);
            }
        }
        return $query;
    }

    /**
     * @param
     *
     * @return string
     */
    public function generateInsertQuery()
    {
        $columns = implode(', ', $this->column);
        $value   = implode(', ', array_fill(0, count($this->bindings), '?'));
        $table   = $this->from[0];

        return sprintf('INSERT INTO %s(%s) VALUES(%s)', $table, $columns, $value);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public function generateUpdateQuery(array $data)
    {
        $columns = $values = [];
        foreach ($data as $key => $value) {
            if ($value instanceof Expression) {
                $placeHolder = $value->__toString();
            } else {
                $placeHolder = '?';
                $values[]    = $value;
            }
            $columns[] = $key . ' = ' . $placeHolder;
        }

        array_unshift($this->bindings, ...$values);
        $where = empty($this->where) ? '' : $this->compileWhere($this);

        return sprintf('UPDATE %s SET %s%s', $this->from[0], implode(', ', $columns), $where);
    }

    /**
     * @return string
     */
    public function generateDeleteQuery()
    {
        $where = $this->compileWhere($this);

        return sprintf('DELETE FROM %s %s', $this->from[0], $where);
    }

}

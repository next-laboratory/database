<?php

namespace Max\Database;

use Max\Exception\InvalidArgumentException;

/**
 * Class Builder
 *
 * @package Max\Database
 */
abstract class AbstractBuilder
{

    const ORDER_DESC = 'DESC';
    const ORDER_ASC = 'ASC';

    const SELECT = 'SELECT %s FROM %s%s%s%s%s%s';
    const UPDATE = 'UPDATE %s SET %s%s';
    const INSERT = 'INSERT INTO %s%s%s';
    const DELETE = 'DELETE FROM %s%s';

    /**
     * 绑定的参数
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * 数据表前缀
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * 字段列表
     *
     * @var string
     */
    protected $fields = '';

    /**
     * LIMIT
     *
     * @var string
     */
    protected $limit = '';

    /**
     * GROUP BY
     *
     * @var string
     */
    protected $group = '';

    /**
     * HAVING
     *
     * @var string
     */
    protected $having = '';

    /**
     * JOIN
     *
     * @var string
     */
    protected $join = '';

    /**
     * ORDER BY
     *
     * @var string
     */
    protected $order = '';

    /**
     * 表名
     *
     * @var string
     */
    protected $table = '';

    /**
     * WHERE 子句
     *
     * @var string
     */
    protected $where = '';

    /**
     * 获取whereSQL
     *
     * @return string
     */
    protected function getWhere(): string
    {
        return empty($this->where) ? '' : (' WHERE ' . substr($this->where, 5));
    }

    /**
     * 绑定参数取得
     *
     * @return array
     */
    public function getBindParams()
    {
        return $this->bindings;
    }

    /**
     *  where条件表达式
     *
     * @param        $keyOrArray
     * @param string $operatorOrValue
     * @param null   $value
     *
     * @return $this
     */
    public function where($keyOrArray, string $operatorOrValue = '=', $value = null)
    {
        if (is_array($keyOrArray)) {
            foreach ($keyOrArray as $k => $v) {
                $this->jointWhere($k, $v, $operatorOrValue);
            }
        } else {
            $this->jointWhere($keyOrArray, $value, $operatorOrValue);
        }
        return $this;
    }

    protected function jointWhere($key, $value, $operator)
    {
        $this->where        .= " AND {$key} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * 模糊查询
     *
     * @param array $keyOrArray
     * @param null  $value
     *
     * @return $this
     */
    public function whereLike(array $keyOrArray, $value = null)
    {
        return $this->where($keyOrArray, 'LIKE', $value);
    }

    /**
     * WHERE NULL
     *
     * @param string|array $whereNull
     *
     * @return $this
     */
    public function whereNull(string $nullField)
    {
        $this->where .= " AND {$nullField} IS NULL";
        return $this;
    }

    /**
     * WHERE NOT NULL
     *
     * @param array|string $whereNotNull
     *
     * @return $this
     */
    public function whereNotNull(string $notNullField)
    {
        $this->where .= " AND {$notNullField} IS NOT NULL";
        return $this;
    }

    /**
     * WHERE OR
     *
     * @param array  $whereOr
     * @param string $operator
     *
     * @return $this
     */
    public function whereOr(array $whereOr, string $operator = '=')
    {
        foreach ($whereOr as $key => $value) {
            if (is_numeric($key)) {
                $this->where .= " OR {$value}";
            } else {
                $this->where        .= " OR {$key} {$operator} ?";
                $this->bindings[] = $value;
            }
        }
        return $this;
    }

    /**
     * WHERE IN
     *
     * @param array $whereIn
     *
     * @return $this
     */
    public function whereIn(array $whereIn)
    {
        foreach ($whereIn as $column => $range) {
            $range       = (array)$range;
            $bindStr     = rtrim(str_repeat('?,', count($range)), ',');
            $this->where .= " AND {$column} IN ({$bindStr})";
            array_push($this->bindings, ...array_values($range));
        }
        return $this;
    }

    public function whereNotIn($whereNotIn)
    {
        foreach ($whereNotIn as $column => $range) {
            $range       = (array)$range;
            $bindStr     = rtrim(str_repeat('?,', count($range)), ',');
            $this->where .= " AND {$column} NOT IN ({$bindStr})";
            array_push($this->bindings, ...$range);
        }
        return $this;
    }

    /**
     * WHERE BETWEEN
     *
     * @param array $whereBetween
     *
     * @return $this
     */
    public function whereBetween(array $whereBetween)
    {
        foreach ($whereBetween as $field => $value) {
            if (is_numeric($field)) {
                $this->where .= " AND {$value}";
            } else if (2 === count($value)) {
                $this->where .= " AND {$field} BETWEEN ? AND ?";
                array_push($this->bindings, ...$value);
            } else {
                throw new InvalidArgumentException('whereBetween参数有误');
            }
        }
        return $this;
    }

    /**
     * WHERE EXISTS
     *
     * @param array $whereExists
     *
     * @return $this
     */
    public function whereExists(array $whereExists)
    {
        foreach ($whereExists as $exist) {
            $this->where .= " AND EXISTS({$exist})";
        }
        return $this;
    }

    public function whereNotExists(array $whereNotExists)
    {
        foreach ($whereNotExists as $exist) {
            $this->where .= " AND NOT EXISTS({$exist})";
        }
        return $this;
    }

    /**
     * @return string
     */
    protected function getTable(): string
    {
        if ('' === $this->table) {
            throw new \Exception('没有指定表名！');
        }
        return $this->table;
    }

    /**
     * 表明设置方法，不包含前缀
     *
     * @param string $table
     *
     * @return $this
     */
    public function name(string $table, string $alias = null)
    {
        return $this->table($this->prefix . $table, $alias);
    }

    /**
     * 带前缀的表名
     *
     * @param string $table
     *
     * @return $this
     */
    public function table(string $table, string $alias = null)
    {
        if (isset($alias)) {
            $alias = " AS {$alias}";
        }
        $this->table = "{$table}{$alias}";
        return $this;
    }

    /**
     * order排序操作，支持多字段排序
     *
     * @param array $order
     * 传入数组形式的排序字段，例如['id' => 'desc','name' => 'asc']
     *
     * @return $this
     */
    public function order(string $order, $sort = 'ASC')
    {
        $this->order .= ", {$order} {$sort}";
        return $this;
    }

    protected function getOrder(): string
    {
        if ('' !== $this->order) {
            $this->order = ' ORDER BY' . ltrim($this->order, ',');
        }
        return $this->order;
    }

    /**
     * 内联
     *
     * @param array $joinTables
     *
     * @return $this
     * @throws \Exception
     */
    public function join(string $table, string $on = '', string $type = 'INNER')
    {
        $this->join .= " {$type} JOIN {$table}" . (('' == $on) ? '' : ' ON ' . $on);
        return $this;
    }

    /**
     * 左联
     *
     * @param array $joinTables
     *
     * @return $this
     * @throws \Exception
     */
    public function leftJoin(string $table, string $on)
    {
        return $this->join($table, $on, 'LEFT OUTER');
    }

    /**
     * 右联
     *
     * @param array $joinTables
     *
     * @return $this
     * @throws \Exception
     */
    public function rightJoin(string $table, string $on)
    {
        return $this->join($table, $on, 'RIGHT OUTER');
    }

    public function crossJoin(string $table)
    {
        return $this->join($table, '', 'CROSS');
    }

    /**
     * @param string|array $fields
     *
     * @return $this
     */
    public function fields($fields = '*')
    {
        if (is_string($fields) && '*' !== $fields) {
            $this->fields .= ",{$fields}";
        }
        if (is_array($fields)) {
            $this->fields .= ',' . implode(',', $fields);
        }
        return $this;
    }

    public function getFields()
    {
        return $this->fields ? ltrim($this->fields, ',') : '*';
    }

    /**
     * GROUP BY
     *
     * @param array $group
     *
     * @return $this
     */
    public function group(string $groupBy, string $having = '')
    {
        $this->group .= ', ' . $groupBy;
        if ('' !== $having) {
            $this->having .= ' AND ' . $having;
        }
        return $this;
    }

    /**
     * 取得group子句
     *
     * @return string
     */
    protected function getGroup(): string
    {
        if ('' !== $this->group) {
            $this->group = ' GROUP BY' . substr($this->group, 1);
            if ('' !== $this->having) {
                $this->having = ' HAVING' . substr($this->having, 4);
            }
        }
        return $this->group . $this->having;
    }

    /**
     * limit
     *
     * @param int      $limit
     * @param int|null $offset
     *
     * @return $this
     */
    public function limit(int $limit, int $offset = null)
    {
        $this->limit = ' LIMIT ' . (isset($offset) ? "{$offset},{$limit}" : $limit);
        return $this;
    }

    /**
     * 查询
     *
     * @return Collection
     * 数据集对象
     * @throws \Exception
     */
    public function select($field = null): string
    {
        if (isset($field)) {
            $this->fields($field);
        }
        return sprintf(static::SELECT,
            $this->getFields(),
            $this->getTable(),
            $this->join,
            $this->getWhere(),
            $this->getGroup(),
            $this->getOrder(),
            $this->limit
        );
    }

    /**
     * 删除数据
     *
     * @return string
     * 影响的行数
     * @throws \Exception
     */
    public function delete(): string
    {
        return sprintf(static::DELETE,
            $this->getTable(),
            $this->getWhere()
        );
    }

    /**
     * 更新
     *
     * @param array $data
     *
     * @return string
     * @throws \Exception
     */
    public function update(array $data): string
    {
        $set = '';
        foreach ($data as $field => $value) {
            $set .= "{$field} = ? , ";
        }
        $set = substr($set, 0, -3);
        array_unshift($this->bindings, ...array_values($data));
        return sprintf(static::UPDATE,
            $this->getTable(),
            $set,
            $this->getWhere()
        );
    }

    /**
     * INSERT 语句取得
     *
     * @param array $data
     *
     * @return string
     * @throws \Exception
     */
    public function insert(array $data)
    {
        $this->bindings = [];
        $columns          = '';
        if (array_keys($data) !== range(0, count($data) - 1)) {
            $columns = ' (' . implode(',', array_keys($data)) . ')';
        }
        $values = ' VALUES (' . rtrim(str_repeat('?,', count($data)), ',') . ')';
        array_push($this->bindings, ...array_values($data));
        return sprintf(static::INSERT,
            $this->getTable(),
            $columns,
            $values
        );
    }

}

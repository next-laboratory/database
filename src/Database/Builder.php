<?php

namespace Max\Database;

use Max\Tools\Arr;

/**
 * Class Builder
 * @package Max\Database
 */
class Builder
{

    const ORDER_DESC = 'DESC';
    const ORDER_ASC = 'ASC';

    const SELECT = 'SELECT %s FROM %s%s%s%s%s%s';
    const UPDATE = 'UPDATE %s SET %s%s';
    const INSERT = 'INSERT INTO %s%s%s';
    const DELETE = 'DELETE FROM %s%s';

    /**
     * 绑定的参数
     * @var array
     */
    protected $bindParams = [];

    /**
     * 数据表前缀
     * @var string
     */
    protected $prefix = '';

    /**
     * 字段列表
     * @var string
     */
    protected $fields = '';

    /**
     * LIMIT
     * @var string
     */
    protected $limit = '';

    /**
     * GROUP BY
     * @var string
     */
    protected $group = '';

    /**
     * HAVING
     * @var string
     */
    protected $having = '';

    /**
     * JOIN
     * @var string
     */
    protected $join = '';

    /**
     * ORDER BY
     * @var string
     */
    protected $order = '';

    /**
     * 表名
     * @var string
     */
    protected $table = '';

    /**
     * WHERE 子句
     * @var string
     */
    protected $where = '';

    /**
     * 获取whereSQL
     * @return string
     */
    protected function getWhere(): string
    {
        return empty($this->where) ? '' : (' WHERE ' . substr($this->where, 5));
    }

    /**
     * 绑定参数取得
     * @return array
     */
    public function getBindParams()
    {
        return $this->bindParams;
    }

    /**
     * where条件表达式
     * @param array $where
     * @param string $operator
     * @return $this
     */
    public function where(array $where, string $operator = '=')
    {
        foreach ($where as $key => $value) {
            if (is_numeric($key)) {
                $this->where .= " AND {$value}";
            } else {
                $this->where        .= " AND {$key} {$operator} ?";
                $this->bindParams[] = $value;
            }
        }
        return $this;
    }

    /**
     * 模糊查询
     * @param array $whereLike
     * @return $this
     */
    public function whereLike(array $whereLike)
    {
        foreach ($whereLike as $field => $like) {
            if (is_numeric($field)) {
                $this->where .= " AND {$like}";
            } else {
                $this->where        .= " AND {$field} LIKE ?";
                $this->bindParams[] = $like;
            }
        }
        return $this;
    }

    /**
     * WHERE NULL
     * @param string|array $whereNull
     * @return $this
     */
    public function whereNull($whereNull)
    {
        if (is_array($whereNull)) {
            foreach ($whereNull as $field) {
                $this->where .= " AND {$field} IS NULL";
            }
        } else {
            $this->where .= " AND {$whereNull} IS NULL";
        }
        return $this;
    }

    /**
     * WHERE NOT NULL
     * @param array|string $whereNotNull
     * @return $this
     */
    public function whereNotNull($whereNotNull)
    {
        if (is_array($whereNotNull)) {
            foreach ($whereNotNull as $field) {
                $this->where .= " AND {$field} IS NOT NULL";
            }
        } else {
            $this->where .= " AND {$whereNotNull} IS NOT NULL";
        }
        return $this;
    }

    /**
     * WHERE OR
     * @param array $whereOr
     * @param string $operator
     * @return $this
     */
    public function whereOr(array $whereOr, string $operator = '=')
    {
        foreach ($whereOr as $key => $value) {
            if (is_numeric($key)) {
                $this->where .= " OR {$value}";
            } else {
                $this->where        .= " OR {$key} {$operator} ?";
                $this->bindParams[] = $value;
            }
        }
        return $this;
    }

    /**
     * WHERE IN
     * @param array $whereIn
     * @return $this
     */
    public function whereIn(array $whereIn)
    {
        foreach ($whereIn as $column => $range) {
            $range       = (array)$range;
            $bindStr     = rtrim(str_repeat('?,', count($range)), ',');
            $this->where .= " AND {$column} IN ({$bindStr})";
            array_push($this->bindParams, ...array_values($range));
        }
        return $this;
    }

    public function whereNotIn($whereNotIn)
    {
        foreach ($whereNotIn as $column => $range) {
            $range       = (array)$range;
            $bindStr     = rtrim(str_repeat('?,', count($range)), ',');
            $this->where .= " AND {$column} NOT IN ({$bindStr})";
            array_push($this->bindParams, ...$range);
        }
        return $this;
    }

    /**
     * WHERE BETWEEN
     * @param array $whereBetween
     * @return $this
     */
    public function whereBetween(array $whereBetween)
    {
        foreach ($whereBetween as $field => $value) {
            if (is_numeric($field)) {
                $this->where .= " AND {$value}";
            } else if (2 === count($value)) {
                $this->where .= " AND {$field} BETWEEN ? AND ?";
                array_push($this->bindParams, ...$value);
            }
        }
        return $this;
    }

    /**
     * WHERE EXISTS
     * @param array $whereExists
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
     * @param string $table
     * @return $this
     */
    public function name(string $table, string $alias = '')
    {
        if ('' !== $alias) {
            $alias = " AS {$alias}";
        }
        $this->table = "{$this->prefix}{$table}{$alias}";
        return $this;
    }

    /**
     * 带前缀的表名
     * @param string $table
     * @return $this
     */
    public function table(string $table, string $alias = '')
    {
        if ('' !== $alias) {
            $alias = " AS {$alias}";
        }
        $this->table = "{$table}{$alias}";
        return $this;
    }

    /**
     * order排序操作，支持多字段排序
     * @param array $order
     * 传入数组形式的排序字段，例如['id' => 'desc','name' => 'asc']
     * @return $this
     */
    public function order(array $order)
    {
        foreach ($order as $ord => $by) {
            $this->order .= ", {$ord} {$by}";
        }
        return $this;
    }

    protected function getOrder(): string
    {
        if ('' !== $this->order) {
            $this->order = ' ORDER BY' . ltrim($this->order, ',');
        }
        return $this->order;
    }

    protected function leagueTableMethod($joinTables, $method = 'INNER')
    {
        foreach (Arr::getAssoc($joinTables) as $joinTable => $on) {
            if ('INNER' !== $method && null === $on) {
                throw new \Exception("{$method} 联表必须有限定条件！");
            }
            $this->join .= " {$method} JOIN {$joinTable}" . (is_null($on) ? '' : ' ON ' . $on);
        }
    }

    /**
     * 内联
     * @param array $joinTables
     * @return $this
     * @throws \Exception
     */
    public function join(array $joinTables)
    {
        $this->leagueTableMethod($joinTables);
        return $this;
    }

    /**
     * 左联
     * @param array $joinTables
     * @return $this
     * @throws \Exception
     */
    public function leftJoin(array $joinTables)
    {
        $this->leagueTableMethod($joinTables, 'LEFT OUTER');
        return $this;
    }

    /**
     * 右联
     * @param array $joinTables
     * @return $this
     * @throws \Exception
     */
    public function rightJoin(array $joinTables)
    {
        $this->leagueTableMethod($joinTables, 'RIGHT OUTER');
        return $this;
    }

    /**
     * @param string|array $fields
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
     * @param array $group
     * @return $this
     */
    public function group(array $group)
    {
        foreach ($group as $groupBy => $having) {
            if (is_numeric($groupBy)) {
                $this->group .= "{$having}, ";
            } else {
                $this->group  .= $groupBy . ', ';
                $this->having .= " AND {$having}";
            }
        }
        return $this;
    }

    /**
     * 取得group子句
     * @return string
     */
    protected function getGroup(): string
    {
        if ('' !== $this->group) {
            $this->group = ' GROUP BY ' . trim($this->group, ', ');
            if ('' !== $this->having) {
                $this->group .= ' HAVING' . substr($this->having, 4);
            }
        }
        return $this->group;
    }

    /**
     * limit
     * @param int $limit
     * @param int|null $offset
     * @return $this
     */
    public function limit(int $limit, int $offset = null)
    {
        $this->limit = ' LIMIT ' . (isset($offset) ? "{$offset},{$limit}" : $limit);
        return $this;
    }

    /**
     * 查询
     * @return Collection
     * 数据集对象
     * @throws \Exception
     */
    public function select(string $field = null): string
    {
        if (isset($field)) {
            $this->fields = $field;
        }
        return sprintf(static::SELECT, $this->getFields(), $this->getTable(), $this->join, $this->getWhere(), $this->getGroup(), $this->getOrder(), $this->limit);
    }

    /**
     * 删除数据
     * @return string
     * 影响的行数
     * @throws \Exception
     */
    public function delete(): string
    {
        return sprintf(static::DELETE, $this->getTable(), $this->getWhere());
    }

    /**
     * 更新
     * @param array $data
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
        array_unshift($this->bindParams, ...array_values($data));
        return sprintf(static::UPDATE, $this->getTable(), $set, $this->getWhere());
    }

    /**
     * INSERT 语句取得
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function insert(array $data)
    {
        $this->bindParams = [];
        $columns          = '';
        if (Arr::isAssoc($data)) {
            $columns = ' (' . implode(',', array_keys($data)) . ')';
        }
        $values = ' VALUES (' . rtrim(str_repeat('?,', count($data)), ',') . ')';
        //TODO PHP7.4可以使用spread运算符合并数组
        array_push($this->bindParams, ...array_values($data));
        return sprintf(static::INSERT, $this->getTable(), $columns, $values);
    }

}

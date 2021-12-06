<?php

namespace Max\Database\Query\Grammar;

use Max\Database\Contracts\GrammarInterface;
use Max\Database\Query\Builder;
use Max\Database\Query\Expression;
use Max\Database\Query\Join;

class Grammar implements GrammarInterface
{
    /**
     * @var array|string[]
     */
    protected array $select = [
        'aggregate',
        'select',
        'from',
        'join',
        'where',
        'group',
        'having',
        'order',
        'limit',
        'offset',
        'lock'
    ];

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileJoin(Builder $builder)
    {
        $joins = array_map(function(Join $item) {
            $alias = $item->alias ? 'AS ' . $item->alias : '';
            $on    = $item->on ? ('ON ' . implode(' ', $item->on)) : '';
            return ' ' . $item->league . ' ' . $item->table . ' ' . $alias . ' ' . $on;
        }, $builder->join);

        return implode('', $joins);
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileWhere(Builder $builder)
    {
        $whereCondition = [];
        foreach ($builder->where as $where) {
            $whereCondition[] = $where instanceof Expression ? $where->__toString() : implode(' ', $where);
        }
        return ' WHERE ' . implode(' AND ', $whereCondition);
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileFrom(Builder $builder)
    {
        return ' FROM ' . implode(' AS ', array_filter($builder->from));
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileSelect(Builder $builder)
    {
        return implode(', ', $builder->select);
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileLimit(Builder $builder)
    {
        return ' LIMIT ' . $builder->limit;
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileOffset(Builder $builder)
    {
        return ' OFFSET ' . $builder->offset;
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileOrder(Builder $builder)
    {
        $orderBy = array_map(function($item) {
            return $item[0] instanceof Expression ? $item[0]->__toString() : implode(' ', $item);
        }, $builder->order);

        return ' ORDER BY ' . implode(', ', $orderBy);
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileGroup(Builder $builder)
    {
        return ' GROUP BY ' . implode(', ', $builder->group);
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    protected function compileHaving(Builder $builder)
    {
        $having = array_map(function($item) {
            return implode(' ', $item);
        }, $builder->having);

        return ' HAVING ' . implode(' AND ', $having);
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    public function generateSelectQuery(Builder $builder)
    {
        $query = 'SELECT ';
        foreach ($this->select as $value) {
            $compiler = 'compile' . ucfirst($value);
            if (!empty($builder->{$value})) {
                $query .= $this->{$compiler}($builder);
            }
        }
        return $query;
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    public function generateInsertQuery(Builder $builder)
    {
        $columns = implode(', ', $builder->column);
        $value   = implode(', ', array_fill(0, count($builder->bindings), '?'));
        $table   = $builder->from[0];

        return sprintf('INSERT INTO %s(%s) VALUES(%s)', $table, $columns, $value);
    }

    /**
     * @param Builder $builder
     * @param array   $data
     *
     * @return string
     */
    public function generateUpdateQuery(Builder $builder, array $data)
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

        array_unshift($builder->bindings, ...$values);
        $where = empty($builder->where) ? '' : $this->compileWhere($builder);

        return sprintf('UPDATE %s SET %s%s', $builder->from[0], implode(', ', $columns), $where);
    }

    /**
     * @param Builder $builder
     *
     * @return string
     */
    public function generateDeleteQuery(Builder $builder)
    {
        $where = $this->compileWhere($builder);

        return sprintf('DELETE FROM %s %s', $builder->from[0], $where);
    }

}

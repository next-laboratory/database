<?php

namespace Max\Database\Query;

/**
 * @mixin Builder
 */
class Join
{

    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * @var
     */
    public $table;

    /**
     * @var mixed|string
     */
    public $league;

    /**
     * @var mixed|null
     */
    public $alias;

    /**
     * @var
     */
    public $on;

    /**
     * @param Builder $builder
     * @param         $table
     * @param         $alias
     * @param         $league
     */
    public function __construct(Builder $builder, $table, $alias = null, $league = 'INNER JOIN')
    {
        $this->builder = $builder;
        $this->table   = $table;
        $this->league  = $league;
        $this->alias   = $alias;
    }

    /**
     * @param        $first
     * @param        $last
     * @param string $operator
     *
     * @return Builder
     */
    public function on($first, $last, string $operator = '=')
    {
        $this->on = func_get_args();

        return $this->builder;
    }

    /**
     * @param $method
     * @param $args
     *
     * @return Builder
     */
    public function __call($method, $args)
    {
        return $this->builder->{$method}(...$args);
    }
}

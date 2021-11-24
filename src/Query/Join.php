<?php

namespace Max\Database\Query;

/**
 * @mixin Builder
 */
class Join
{

    protected Builder $builder;

    public $table;

    public $league;

    public $alias;

    public $on;

    public function __construct(Builder $builder, $table, $alias = null, $league = 'INNER JOIN')
    {
        $this->builder = $builder;
        $this->table   = $table;
        $this->league  = $league;
        $this->alias   = $alias;
    }

    public function on($first, $operator, $last)
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

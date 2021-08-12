<?php

namespace Max\Database;

class History implements \IteratorAggregate
{

    /**
     * @var array
     */
    protected $items = [];

    /**
     * 记录历史
     * @param $query
     * @param $time
     * @param $binds
     */
    public function record($query, $time, $boundParameters)
    {
        $this->items[] = compact(['query', 'time', 'boundParameters']);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function end()
    {
        return end($this->items);
    }

}

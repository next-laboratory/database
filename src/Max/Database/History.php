<?php


namespace Max\Database;


class History implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $item = [];

    /**
     * 记录历史
     * @param $query
     * @param $time
     * @param $binds
     */
    public function record($query, $time, $bindParams)
    {
        $this->item[] = compact(['query', 'time', 'bindParams']);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->item);
    }
}

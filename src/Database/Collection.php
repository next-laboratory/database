<?php
declare(strict_types=1);

namespace Max\Database;

use ArrayAccess;
use JsonSerializable;
use Countable;

/**
 * 数据集类
 * Class Collection
 * @package Max
 */
class Collection implements ArrayAccess, JsonSerializable, Countable, \IteratorAggregate
{

    /**
     * SQL
     * @var string
     */
    protected $query = '';

    /**
     * 绑定的参数
     * @var array
     */
    protected $bindParams = [];

    /**
     * 查询的数据
     * @var array
     */
    protected $items = [];

    public function __construct(\Closure $closure, string $query = '', array $bindParams = [])
    {
        $this->query      = $query;
        $this->bindParams = $bindParams;
        $this->items      = $closure($this);
        return $this;
    }

    public function throwWhenEmpty(bool $throw = false)
    {
        if (true == $throw && $this->isEmpty()) {
            throw new \Exception('Empty');
        }
        return $this;
    }

    public function getBindParams()
    {
        return $this->bindParams;
    }

    public function getSQL()
    {
        return $this->query;
    }

    public function count()
    {
        return count($this->items);
    }

    /**
     * 判断数据集是否为空
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * 数据集转json方法
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->items);
    }

    /**
     * 数据集转数组方法
     * @return mixed
     */
    public function toArray()
    {
        return $this->items;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if (isset($this->items[$offset])) {
            unset($this->items[$offset]);
            return true;
        }
        return false;
    }

    /**
     * 可以直接json_encode
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->items;
    }

    public function __set($arg, $value)
    {
        $this->$arg = $value;
    }

    public function __get($arg)
    {
        return $this->$arg ?? null;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}

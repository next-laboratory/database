<?php
declare(strict_types=1);

namespace Max\Database;

use Max\App;
use Max\Facade\Db;

/**
 * @method Driver where(array $where);
 * @method Driver whereIn(array $whereIn);
 * @method Driver whereLike(array $whereLike);
 * @method Driver whereNull(array $whereNull);
 * @method Driver whereNotNull(array $whereNotNull);
 * @method Driver limit(int $limit, int $offset);
 * @method insert(array $data);
 * @method Driver field(string|array $fields)
 * Class Model
 * @package Max
 */
class Model
{

    /**
     * 表名
     * @var $name string|null
     */
    protected $name = null;

    /**
     * @var Query
     */
    protected $query;

    /**
     * 默认主键
     * @var $key string
     */
    protected $key = 'id';

    /**
     * 数据集
     * @var \stdClass
     */
    protected $collection;

    /**
     * 初始化表名
     * Model constructor.
     */
    public function __construct()
    {
        $this->name       = $this->name ?? strtolower(ltrim(strrchr(get_called_class(), '\\'), '\\'));
        $this->collection = new \stdClass();
    }

    public function beforeSave()
    {
    }

    protected function modifyAttr()
    {
        $methods = get_class_methods(static::class);
        foreach ($methods as $method) {
            if ('withAttr' === substr($method, 0, 8)) {
                $field                      = strtolower(substr($method, 8));
                $this->collection->{$field} = $this->{$method}($this->collection->{$field});
            }
        }
    }

    /**
     * 获取
     */
    public function getFi()
    {
        $methods = get_class_methods(static::class);
        foreach ($methods as $method) {
            if ('getAttr' === substr($method, 0, 8)) {
                $field                      = strtolower(substr($method, 8));
                $this->collection->{$field} = $this->{$method}($this->collection->{$field});
            }
        }
    }

    public function afterSave()
    {

    }

    public function beforeDelete()
    {
    }

    public function afterDelete()
    {

    }

    public function beforeUpdate()
    {
    }

    public function afterUpdate()
    {

    }

    public function beforeSelect()
    {

    }

    public function afterSelect()
    {

    }

    public function save(array $data = [])
    {
        $this->setData($data);
        return $this->trigger('Save', function () {
            return $this->query->name($this->name)->insert($this->getData());
        });
    }

    protected function getData(): array
    {
        return get_object_vars($this->collection);
    }

    protected function setData(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->collection->{$key} = $value;
        }
    }

    public function update(array $data, array $where = [])
    {
        $this->setData($data);
        return $this->trigger('Update', function () use ($where) {
            return $this->query->name($this->name)->where($where)->update($this->getData());
        });
    }

    public function delete($where)
    {
        $this->trigger('Update', function () use ($where) {
            return $this->query->name($this->name)->where($where)->delete();
        });
    }

    public function trigger($event, \Closure $operation)
    {
        $event  = ucfirst($event);
        $before = $this->{('before' . $event)}();
        $this->modifyAttr();
        $res = $operation($before);
        $this->{('after' . $event)}();
        return $res;
    }

    public function select()
    {
        $results = $this->query->name($this->name)->select();
        foreach ($results as $result) {
            if (is_array($result)) {

            }
        }


        return $this->trigger('Select', function () {
            return $this->query->name($this->name)->select();
        });
    }


    final public function __call($method, $arguments)
    {
        return $this->query->name($this->name)->{$method}(...$arguments);
    }

    public function __get($field)
    {
        return $this->collection->{$field};
    }

    public function __set($field, $value)
    {
        $this->collection->{$field} = $value;
    }

    final public static function __setter(App $app)
    {
        $model        = new static($app);
        $model->query = $app->db;
        return $model;
    }
}

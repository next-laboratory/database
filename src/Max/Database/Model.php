<?php
declare(strict_types=1);

namespace Max\Database;

use Max\App;
use Max\Facade\DB;

/**
 * @method Driver where(array $where);
 * @method Driver whereIn(array $whereIn);
 * @method Driver whereLike(array $whereLike);
 * @method Driver whereNull(array $whereNull);
 * @method Driver whereNotNull(array $whereNotNull);
 * @method Driver limit(int $limit, int $offset);
 * @method insert(array $data);
 * @method Driver field(string|array $fields);
 * Class Model
 * @package Max
 */
class Model
{

    /**
     * 表名
     * @var $name string|null
     */
    protected $table = null;

    protected $fillable = [];

    protected $casts = [];

    protected $timestamps = true;

    /**
     * @var Query
     */
    protected $query;

    /**
     * 默认主键
     * @var $key string
     */
    protected $key = 'id';

    protected $relations = [];

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
        $this->table = $this->table ?? strtolower(ltrim(strrchr(get_called_class(), '\\'), '\\'));
    }

    public function with($relations)
    {
        $this->relations = (array)$relations;
        return $this;
    }

    public function hasOne(string $model, $foreignKey = null, $key = null)
    {
        $foreignKey = $foreignKey ?? $model . '_id';
        $key        = $this->table . '_id';
        return app()->make($model)->where(["$foreignKey = $key"])->select();
    }

    public function select()
    {
        $this->items = DB::name($this->table)->select()->toArray();
        return $this;
    }

    public function get()
    {
        foreach ($this->relations as $relation) {
            $this->each(function ($item) use ($relation) {
                $this->items[$relation] = $this->{$relation}();
            });
        }
        return $this->items;
    }

    final public function __call($method, $arguments)
    {
        return $this->query->name($this->table)->{$method}(...$arguments);
    }

    final public static function __setter(App $app)
    {
        $model        = new static($app);
        $model->query = $app->db;
        return $model;
    }
}

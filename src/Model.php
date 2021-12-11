<?php

namespace Max\Database;

use ArrayAccess;
use Max\Database\Model\Relations\HasOne;
use Max\Utils\Contracts\Arrayable;
use Max\Utils\Traits\HasAttributes;

class Model implements ArrayAccess, Arrayable
{
    /**
     * @var
     */
    protected $table;

    /**
     * @var string
     */
    protected $connect = 'mysql';

    /**
     * @var string
     */
    protected $key = 'id';

    /**
     * @var array
     */
    protected $cast = [];

    /**
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $original = [];

    /**
     * @var array
     */
    protected $hidden = [];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->original = $attributes;
        //        is_null($this->table) && $this->table = Str::camel(class_basename(static::class));

        $this->fill($attributes);
    }

    /**
     * @return array
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }
        return $attributes;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function fill(array $attributes)
    {
        $this->original = $attributes;
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * @param array $columns
     *
     * @return Collection
     * @throws \ReflectionException
     */
    public static function get(array $columns = ['*'])
    {
        return static::query()->get($columns);
    }

    /**
     * @param       $id
     * @param array $columns
     *
     * @return Model
     */
    public static function find($id, array $columns = ['*'])
    {
        return static::query()->find($id, $columns);
    }

    public function findOrFail($id, array $columns = ['*'])
    {
        if ($item = static::query()->find($id, $columns)) {
            return $item;
        }
        throw new \Exception('模型未找到');
    }

    /**
     * @param array $columns
     *
     * @return false|mixed|object
     * @throws \ReflectionException
     */
    public static function first(array $columns = ['*'])
    {
        return static::query()->limit(1)->first($columns);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string      $relation
     * @param string|null $foreignKey
     * @param string|null $localKey
     *
     * @return HasOne
     * @throws \ReflectionException
     */
    public function hasOne(string $relation, ?string $foreignKey, ?string $localKey): HasOne
    {
        /* @var Model $relation */
        $relation = new $relation;
        $relation::query()->where();
        return new HasOne($this->newQuery());
    }

    /**
     * @param array $columns
     *
     * @return Collection
     * @throws \ReflectionException
     */
    public static function all(array $columns = ['*'])
    {
        return static::query()->get($columns);
    }

    /**
     * @return \Max\Database\Model\Builder
     * @throws \ReflectionException
     */
    public static function query()
    {
        return (new static())->newQuery();
    }

    /**
     * @return Query
     * @throws \ReflectionException
     */
    public function newQuery()
    {
        return (new \Max\Database\Model\Builder((new Manager(config('database')))->getConnector($this->connect)))
            ->setModel($this);
    }

    /**
     * @return false|string
     * @throws \ReflectionException
     */
    public function save()
    {
        return self::query()->insert($this->attributes);
    }

    /**
     * @param array $attributes
     *
     * @return false|string
     * @throws \ReflectionException
     */
    public static function create(array $attributes)
    {
        return (new static($attributes))->save();
    }

    /**
     * @param $key
     *
     * @return bool
     */
    protected function hasCast($key)
    {
        return isset($this->cast[$key]);
    }

    /**
     * @param $value
     * @param $cast
     *
     * @return array|float|int|mixed|string
     */
    protected function cast($value, $cast)
    {
        switch ($cast) {
            case 'int':
                $value = intval($value);
                break;
            case 'string':
                $value = strval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
            case 'double':
                $value = doubleval($value);
                break;
            case 'array':
                $value = (array)$value;
                break;
        }
        return $value;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getCast($key)
    {
        return $this->cast[$key];
    }

    /**
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasCast($key)) {
            $value = $this->cast($value, $this->getCast($key));
        }
        $this->attributes[$key] = $value;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @var
     */
    protected $attributes;

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes($attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->original[$key] = $value;
        return $this->setAttribute($key, $value);
    }

    /**
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    /**
     * @param $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * @param $offset
     * @param $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->original[$offset] = $value;
        $this->setAttribute($offset, $value);
    }

    /**
     * @param $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        if ($this->hasAttribute($offset)) {
            unset($this->attributes[$offset]);
        }
    }

}

<?php

namespace Max\Database;

use Max\App;
use Max\Utils\Str;
use Max\Utils\Traits\HasAttributes;

class Model
{

    use HasAttributes;

    protected $table;

    protected $key = 'id';

    protected $cast = [];

    protected $fillable = [];

    protected $original = [];

    protected $hidden = [];

    public function __construct(array $attributes = [])
    {
        is_null($this->table) && $this->table = Str::camel(class_basename(static::class));

        $this->fill($attributes);
    }

    public function getFillable()
    {
        return $this->fillable;
    }

    protected function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }
        return $attributes;
    }

    public function fill(array $attributes)
    {
        $this->original = $attributes;
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public static function get(array $columns = ['*'])
    {
        return static::query()->get($columns);
    }

    public static function find($id, array $columns = [])
    {
        return static::query()->find($id, $columns);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getTable()
    {
        return $this->table;
    }

    public static function all(array $columns = ['*'])
    {
        return static::query()->select($columns);
    }

    /**
     * @return Query
     */
    public static function query()
    {
        return (new static())->newQuery();
    }

    public function newQuery()
    {
        return App::getInstance()
            ->resolve(Query::class)
            ->setModel(static::class)
            ->setTable($this->table)
            ->setPrimaryKey($this->key);
    }

    public function destory()
    {

    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        return $this->setAttribute($key, $value);
    }

    protected function hasCast($key)
    {
        return isset($this->cast[$key]);
    }

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

    public function getCast($key)
    {
        return $this->cast[$key];
    }

    public function setAttribute($key, $value)
    {
        if ($this->hasCast($key)) {
            $value = $this->cast($value, $this->getCast($key));
        }
        $this->attributes[$key] = $value;
    }

}
<?php

namespace Max\Database;

use Max\App;
use Max\Utils\Traits\HasAttributes;

class Model
{

    use HasAttributes;

    protected $table;

    protected $key = 'id';

    protected $cast = [];

    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
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
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
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

    public static function save(array $options = [])
    {

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
            ->table($this->table);
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

    public function hasCast($key)
    {
        return isset($this->cast[$key]);
    }

    public function getCast($key)
    {
        return $this->cast[$key];
    }

    public function setAttribute($key, $value)
    {
        if ($this->hasCast($key)) {
            switch ($this->getCast($key)) {
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
        }
        $this->attributes[$key] = $value;
    }

}
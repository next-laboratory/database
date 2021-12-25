<?php

namespace Max\Database;

use Max\Config\Repository;
use Max\Database\Contracts\ConnectorInterface;
use Max\Database\Query\Builder;

class Manager
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * @var array
     */
    protected array $connectors = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param Repository $repository
     *
     * @return static
     */
    public static function __new(Repository $repository)
    {
        return new static($repository->get('database'));
    }

    /**
     * @param             $name
     * @param null        $alias
     * @param string|null $connection
     *
     * @return Builder
     */
    public function table($name, $alias = null, ?string $connection = null)
    {
        $builder = new Builder($this->getConnector($name));
        return $builder->from($name, $alias);
    }

    /**
     * @param string      $query
     * @param array       $bindings
     * @param string|null $connection
     *
     * @return array|false
     */
    public function select(string $query, array $bindings = [], ?string $connection = null)
    {
        return $this->connect($connection)->run($query, $bindings)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string      $query
     * @param array       $bindings
     * @param string|null $connection
     *
     * @return int
     */
    public function delete(string $query, array $bindings = [], ?string $connection = null)
    {
        return $this->connect($connection)->run($query, $bindings)->rowCount();
    }

    /**
     * @param string      $query
     * @param array       $bindings
     * @param string|null $connection
     *
     * @return false|string
     */
    public function insert(string $query, array $bindings = [], ?string $connection = null)
    {
        $connector = $this->connect($connection);
        $connector->run($query, $bindings);

        return $connector->getPdo()->lastInsertId();
    }

    /**
     * @param string      $query
     * @param array       $bindings
     * @param string|null $connection
     *
     * @return int
     */
    public function exec(string $query, array $bindings = [], ?string $connection = null)
    {
        /** @var \PDOStatement $PDOStatement */
        $PDOStatement = $this->connect($connection)->run($query, $bindings);

        return $PDOStatement->rowCount();
    }

    /**
     * @return void
     */
    public function update()
    {

    }

    /**
     * @param string      $query
     * @param array       $bindings
     * @param string|null $connection
     *
     * @return \Generator
     */
    public function cursor(string $query, array $bindings = [], ?string $connection = null)
    {
        $cursor = $this->connect($connection)->run($query, $bindings);

        while ($record = $cursor->fetch(\PDO::FETCH_ASSOC)) {
            yield $record;
        }
    }

    /**
     * @param string|null $name
     *
     * @return ConnectorInterface
     */
    public function connect(?string $name = null)
    {
        $name = $name ?? $this->config['default'];
        if (!isset($this->connectors[$name])) {
            $config                  = new Config($this->config['connections'][$name]);
            $connector               = $config->getDriver();
            $this->connectors[$name] = new $connector($config);
        }

        return $this->connectors[$name];
    }
}

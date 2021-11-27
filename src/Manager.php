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

    public static function __setter(Repository $repository)
    {
        return new static($repository->get('database'));
    }

    /**
     * @param        $name
     * @param null   $alias
     * @param string $connection
     *
     * @return Builder
     */
    public function table($name, $alias = null, $connection = null)
    {
        return $this->connect($connection)->from($name, $alias);
    }

    public function select(string $query, array $bindings = [], ?string $connection = null)
    {
        return $this->connect($connection)->run($query, $bindings)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function first(string $query, array $bindings = [], ?string $connection = null)
    {
        return $this->connect($connection)->run($query, $bindings)->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete(string $query, array $bindings = [], ?string $connection = null)
    {
        return $this->connect($connection)->run($query, $bindings)->rowCount();
    }

    public function insert(string $query, array $bindings = [], ?string $connection = null)
    {
        $this->connect($connection)->run($query, $bindings);

        return $this->getConnector($connection)->getPdo()->lastInsertId();
    }

    public function update()
    {

    }

    public function cursor(string $query, array $bindings = [], ?string $connection = null)
    {
        $cursor = $this->connect($connection)->run($query, $bindings);

        while ($record = $cursor->fetch(\PDO::FETCH_ASSOC)) {
            yield $record;
        }
    }

    /**
     * @param string $name
     *
     * @return Builder
     */
    public function connect(?string $name = null)
    {
        $name = $name ?? $this->config['default'];
        if (!$this->hasConnection($name)) {
            $config                  = new Config($this->config['connections'][$name]);
            $connector               = $config->getDriver();
            $this->connectors[$name] = new $connector($config);
        }

        return new Builder($this->getConnector($name));
    }

    /**
     * @param $name
     *
     * @return ConnectorInterface
     */
    public function getConnector($name): ConnectorInterface
    {
        return $this->connectors[$name];
    }

    /**
     * @param                    $name
     * @param ConnectorInterface $connection
     */
    public function setConnector($name, ConnectorInterface $connection)
    {
        $this->connectors[$name] = $connection;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->connectors[$name]);
    }
}

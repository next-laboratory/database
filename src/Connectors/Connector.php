<?php

namespace Max\Database\Connectors;

use Max\Database\Config;
use Max\Database\Contracts\ConnectorInterface;
use Max\Database\Contracts\GrammarInterface;
use Max\Database\Query\Grammar\Grammar;
use Max\Database\Query\Grammar\MySqlGrammar;
use Max\Database\Query\Grammar\PgSqlGrammar;

abstract class Connector implements ConnectorInterface
{
    /**
     * @var \PDO
     */
    protected \PDO $PDO;

    /**
     * PDO驱动名
     *
     * @var string
     */
    protected string $driver = '';

    protected ?GrammarInterface $grammar;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->PDO    = new \PDO(
            $this->getDsn($config),
            $config->getUser(),
            $config->getPassword(),
            $config->getOptions()
        );
        $this->getGrammar();
    }

    public function getGrammar(): GrammarInterface
    {
        if (!isset($this->grammar)) {
            switch (true) {
                case $this instanceof MySqlConnector:
                    $this->grammar = new MySqlGrammar();
                    break;
                case $this instanceof PsqlConnector:
                    $this->grammar = new PgSqlGrammar();
                    break;
                default:
                    $this->grammar = new Grammar();
                    break;
            }
        }

        return $this->grammar;
    }

    /**
     * @param Config $config
     *
     * @return string
     */
    public function getDsn(Config $config): string
    {
        if (empty($dsn = $config->getDsn())) {
            $dsn = sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s',
                $this->driver,
                $config->getHost(),
                $config->getPort(),
                $config->getDatabase(),
                $config->getCharset()
            );
        }
        return $dsn;
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return false|\PDOStatement
     */
    public function statement(string $query, array $bindings = [])
    {
        $statement = $this->getPdo()->prepare($query);

        $this->bindValue($statement, $bindings);

        return $statement;
    }

    /**
     * @param \PDOStatement $PDOStatement
     * @param array         $bindings
     */
    protected function bindValue(\PDOStatement $PDOStatement, array $bindings)
    {
        foreach ($bindings as $key => $value) {
            $PDOStatement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR
            );
        }
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->PDO;
    }

    public function select($query, array $bindings = [])
    {
        $PDOStatement = $this->statement($query, $bindings);

        $PDOStatement->execute();

        return $PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
    }

}


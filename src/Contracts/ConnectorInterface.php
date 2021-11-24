<?php

namespace Max\Database\Contracts;

use Max\Database\Config;

interface ConnectorInterface
{
    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return \PDOStatement | false
     */
    public function statement(string $query, array $bindings);

    public function getPdo(): \PDO;

    public function getDsn(Config $config): string;
}

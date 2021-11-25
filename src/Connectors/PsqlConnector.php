<?php

namespace Max\Database\Connectors;

class PsqlConnector extends Connector
{
    protected string $driver = 'pgsql';

    protected string $grammar = '\Max\Database\Query\Grammar\PgSqlGrammar';
}

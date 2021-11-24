<?php

namespace Max\Database;

use PDO;

class Config
{
    protected ?string $dsn;
    protected string  $driver;
    protected string  $grammar;
    protected string  $database;
    protected string  $host     = '127.0.0.1';
    protected ?string $user     = null;
    protected ?string $password = null;
    protected int     $port     = 3306;
    protected array   $options  = [];
    protected string  $charset  = 'utf8';
    protected string  $prefix   = '';

    protected array $defaultOptions = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @return string
     */
    public function getGrammar(): string
    {
        return $this->grammar;
    }

    /**
     * @return string|null
     */
    public function getDsn(): ?string
    {
        return $this->dsn ?: "mysql:{$this->database}:host={$this->getHost()};port={$this->getPort()};dbname={$this->getDatabase()};";
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return array_merge($this->defaultOptions, $this->options);
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}

<?php

namespace Max\Database;

class Config
{
    /**
     * @var string|null
     */
    protected ?string $dsn;

    /**
     * @var string
     */
    protected string $driver;

    /**
     * @var string
     */
    protected string $grammar;

    /**
     * @var string
     */
    protected string $database;

    /**
     * @var string
     */
    protected string $host = '127.0.0.1';

    /**
     * @var string|null
     */
    protected ?string $user = null;

    /**
     * @var string|null
     */
    protected ?string $password = null;

    /**
     * @var int
     */
    protected int $port = 3306;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var string
     */
    protected string $charset = 'utf8';

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @param array $config
     */
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
        return $this->options;
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

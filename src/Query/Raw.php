<?php

namespace Max\Database\Query;

class Raw
{
    protected $statement;

    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    public function __toString()
    {
        return $this->statement;
    }
}

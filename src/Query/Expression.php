<?php

namespace Max\Database\Query;

class Expression
{
    protected string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function __toString()
    {
        return $this->expression;
    }
}

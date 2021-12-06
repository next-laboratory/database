<?php

namespace Max\Database\Query;

class Expression
{
    /**
     * @var string
     */
    protected string $expression;

    /**
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }
}

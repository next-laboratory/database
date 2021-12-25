<?php

namespace Max\Database\Query;

/**
 * @class   Expression
 * @author  ChengYao
 * @date    2021/12/25
 * @time    11:48
 * @package Max\Database\Query
 */
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
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }
}

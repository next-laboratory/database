<?php
declare(strict_types=1);

namespace Max\Database\Builder;

use Max\Database\AbstractBuilder;

/**
 * Class Mysql
 * @package Max\Db\Drivers
 */
class Mysql extends AbstractBuilder
{

    protected function quote(string $var): string
    {
        $var = trim($var, '`');
        return "`{$var}`";
    }

    public function table(string $table, string $alias = null)
    {
        return parent::table($this->quote($table), $alias ? $this->quote($alias) : $alias);
    }

}

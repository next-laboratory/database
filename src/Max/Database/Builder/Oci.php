<?php

namespace Max\Database\Builder;

use Max\Database\AbstractBuilder;

class Oci extends AbstractBuilder
{
    /**
     * @param int $limit
     * @param int|null $offset
     * @return Oci|void
     * @throws \Exception
     */
    public function limit(int $limit, int $offset = null)
    {
        throw new \Exception('Oracle数据库不支持limit子句！');
    }

}

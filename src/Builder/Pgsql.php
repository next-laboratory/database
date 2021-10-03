<?php
declare(strict_types=1);

namespace Max\Database\Builder;

use Max\Database\AbstractBuilder;

/**
 * Class Pgsql
 * @package Max\Database\Builder
 */
class Pgsql extends AbstractBuilder
{

    /**
     * @param int $limit
     * @param int|null $offset
     * @return $this|Pgsql
     */
    public function limit(int $limit, int $offset = null)
    {
        $this->limit = ' LIMIT ' . $limit . ($offset ? 'OFFSET ' . $offset : '');
        return $this;
    }


}

<?php
declare(strict_types=1);

namespace Max\Database\Builder;

use Max\Database\Builder;

/**
 * Class Pgsql
 * @package Max\Database\Builder
 */
class Pgsql extends Builder
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

<?php

namespace Max\Facade;

use Max\Facade;

/**
 * Class DB
 * @package Max\Facade
 * @method static \Max\Database\Query name(string $table_name) 表名设置方法
 * @method static \Max\Database\Query table(string $table_name) 表名设置方法
 * @method static mixed query(string $sql, array $data = [], bool $all = true) 查询
 * @method static mixed transaction(\Closure $transaction) 事务操作
 * @method static integer exec(string $sql, array $data = []) 增删改
 * @method static array getHistory() 查询历史SQL和执行时间
 */
class DB extends Facade
{

    protected static function getFacadeClass()
    {
        return 'db';
    }

}


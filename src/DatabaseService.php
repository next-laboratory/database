<?php
declare(strict_types=1);

namespace Max;

use Max\Database\Query;

class DatabaseService extends Service
{

    public function register()
    {
        $this->app->bind('db', \Max\Database\Query::class);
    }

    public function boot()
    {
    }

}

if (false === function_exists('db')) {
    /**
     * DB类助手函数
     * @param string $tableName
     * @return Query
     */
    function db(string $tableName)
    {
        return app('db')->name($tableName);
    }
}

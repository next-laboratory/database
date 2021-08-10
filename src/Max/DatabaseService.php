<?php
declare(strict_types=1);

namespace Max {

    use Max\Database\Query;

    class DatabaseService extends Service
    {

        public function register()
        {
            $this->app->alias('db', \Max\Database\Query::class);
        }

        public function boot()
        {
        }

    }
}

/**
 * DB类助手函数
 * @param string|null $tableName
 * @return \Max\Database\Query
 */
function db(string $tableName = null)
{
    return is_null($tableName) ? app('db') : app('db')->name($tableName);
}

<?php
declare(strict_types=1);

namespace Max\Database {

    use Max\Database\Query;

    class DatabaseServiceProvider extends ServiceProvider
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

namespace {

    /**
     * DB类助手函数
     * @param string|null $tableName
     * @return \Max\Database\Query
     */
    function db(string $tableName = null)
    {
        return is_null($tableName) ? make('db') : make('db')->name($tableName);
    }
}


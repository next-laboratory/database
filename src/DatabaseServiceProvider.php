<?php
declare(strict_types=1);

namespace Max\Database {

    use Max\Foundation\ServiceProvider;

    class DatabaseServiceProvider extends ServiceProvider
    {
        public function register()
        {
            $this->app->alias('db', Query::class);
        }

        public function boot()
        {
        }

    }
}

namespace {

    if (false === function_exists('db')) {
        /**
         * DB类助手函数
         *
         * @param string|null $tableName
         *
         * @return \Max\Database\Query
         */
        function db(string $tableName = null)
        {
            return is_null($tableName) ? make('db') : make('db')->name($tableName);
        }
    }

}


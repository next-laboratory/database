<?php
declare(strict_types=1);

namespace {

    use Max\Database\Driver;

    if (false === function_exists('db')) {
        /**
         * db类助手函数
         * @param string $tableName
         * @return Driver
         */
        function db(string $tableName)
        {
            return app('db')->name($tableName);
        }
    }

}

namespace Max {

    use Max\Service;

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
}

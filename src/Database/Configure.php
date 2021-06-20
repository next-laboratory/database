<?php


namespace Max\Database;


class Configure
{

    public static function install()
    {
        $root = getcwd();
        if (!file_exists($root . '/config/database.php')) {
            if (copy(__DIR__ . '/../database.php', $root . '/config/database.php')) {
                echo "\033[32m Generate config file successfully: /config/database.php \033[0m \n";
            }
        }
    }

    public static function remove()
    {

    }

}
<?php

return [
    'connections' => [
        'default' => [
            'driver'   => '\Max\Database\Connectors\MySqlConnector',
            'grammar'  => '\Max\Database\Query\Grammar\MySqlGrammar',
            //可以使用dsn来配置更多参数，会优先使用该参数
            'dsn'      => '',
            //主机地址
            'host'     => env('database.host', 'localhost'),
            //数据库用户名
            'user'     => env('database.user', 'user'),
            //数据库密码
            'password' => env('database.pass', 'pass'),
            //数据库名
            'database' => env('database.dbname', 'name'),
            //端口
            'port'     => env('database.port', 3306),
            //额外设置
            'options'  => [],
            //编码
            'charset'  => env('database . charset', 'utf8mb4'),
            //数据表前缀
            'prefix'   => '',
            //主
            'master'   => [],
            //从
            'slave'    => []
        ],
    ],


];

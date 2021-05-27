<?php

return [

    //默认数据库配置
    'default'  => env('database.default', 'mysql'),
    //慢SQL日志记录时间,数字（ms，当执行时间不小于该时间时会记录日志）或者false(关闭日志) ,0 全部记录
    'slow_log' => 5,

    'mysql' => [
        //可以使用dsn来配置更多参数，会优先使用该参数
        'dsn'     => '',
        //主机地址
        'host'    => env('database.host', 'localhost'),
        //数据库用户名
        'user'    => env('database.user', 'user'),
        //数据库密码
        'pass'    => env('database.pass', 'pass'),
        //数据库名
        'dbname'  => env('database.dbname', 'dbname'),
        //端口
        'port'    => env('database.port', 3306),
        //额外设置
        'options' => env('database.options', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]),
        //编码
        'charset' => env('database . charset', 'utf8mb4'),
        //数据表前缀
        'prefix'  => '',
        //主
        'master'  => [],
        //从
        'slave'   => []
    ],

//    'pgsql' => [
//        'dsn'     => '',
//        //主机地址
//        'host'    => env('database.host', 'localhost'),
//        //数据库用户名
//        'user'    => env('database.user', 'user'),
//        //数据库密码
//        'pass'    => env('database.pass', 'pass'),
//        //数据库名
//        'dbname'  => env('database.dbname', 'dbname'),
//        //端口
//        'port'    => env('database.port', 5432),
//        //额外设置
//        'options' => env('database.options', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]),
//        //编码
//        'charset' => env('database . charset', 'utf8'),
//        //数据表前缀
//        'prefix'  => ''
//    ],

//    'oci' => [
//        'dsn'     => '',
//        //主机地址
//        'host'    => env('database.host', 'localhost'),
//        //数据库用户名
//        'user'    => env('database.user', 'user'),
//        //数据库密码
//        'pass'    => env('database.pass', 'pass'),
//        //数据库名
//        'dbname'  => env('database.dbname', 'dbname'),
//        //端口
//        'port'    => env('database.port', 5432),
//        //额外设置
//        'options' => env('database.options', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]),
//        //编码
//        'charset' => env('database . charset', 'utf8'),
//        //数据表前缀
//        'prefix'  => ''
//    ]

];

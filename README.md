<br>

<p align="center">
<img src="https://raw.githubusercontent.com/topyao/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D7.2.0-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

Max框架数据库组件

# 安装

> 该扩展依赖于MaxPHP,Max-Framework ，所以需要先安装MaxPHP

```shell
composer create-project max/max
```

```shell
composer require max/database
```

# 使用

## 注册服务提供者

在`/config/app.php` 的`provider`下的`http`中注册服务提供者类`\Max\DatabaseService::class`

## 配置文件

安装完成后框架会自动将配置文件`database.php`移动到根包的config目录下，如果创建失败，可以手动创建。

文件内容如下：

```php
<?php

return [

    //默认数据库配置
    'default'     => env('database.default', 'mysql'),

    'mysql' => [
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
        'prefix'  => ''
    ],

/*    'pgsql' => [
        //主机地址
        'host'    => env('database.host', 'localhost'),
        //数据库用户名
        'user'    => env('database.user', 'user'),
        //数据库密码
        'pass'    => env('database.pass', 'pass'),
        //数据库名
        'dbname'  => env('database.dbname', 'dbname'),
        //端口
        'port'    => env('database.port', 5432),
        //额外设置
        'options' => env('database.options', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]),
        //编码
        'charset' => env('database . charset', 'utf8'),
        //数据表前缀
        'prefix'  => ''
    ]*/
];
```

## 方法

安装完成后就可以使用`\Max\Facade\Db::name($table);`等的方式来使用缓存扩展，或者使用助手函数`db($tableName)`

> 官网：https://www.chengyao.xyz

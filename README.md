<br>

<p align="center">
<img src="https://raw.githubusercontent.com/topyao/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D7.4-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>

Max框架数据库组件

# 安装

> 使用该包需要先安装MaxPHP

<a href="https://github.com/topyao/max/blob/master/README.md">README.md</a>

## 安装

```shell
composer require max/database
```

## 安装开发版

```shell
composer require max/database:dev-master
```

# 使用

## 注册服务提供者

在`\App\Http\Kernel::class` 或者`\App\Console\Kernel::class`中的`providers`下注册服务提供者类`\Max\Database\DatabaseServiceProvider::class`

## 配置文件

安装完成后框架会自动将配置文件`database.php`移动到根包的config目录下，如果创建失败，可以手动创建。

文件内容如下：

```php
<?php

return [

    //默认数据库配置
    'default'  => env('database.default', 'mysql'),

    // mysql, pgsql, oci
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

];

```

## 方法

安装完成后就可以使用`\Max\Facade\DB::name($table);`等的方式来使用Database扩展，或者使用助手函数`db($tableName)`

> 官网：https://www.chengyao.xyz

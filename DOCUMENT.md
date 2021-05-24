<br>

<p align="center">
<img src="https://raw.githubusercontent.com/topyao/max/master/public/favicon.ico" width="120" alt="Max">
</p>

<p align="center">轻量 • 简单 • 快速</p>

<p align="center">
<img src="https://img.shields.io/badge/php-%3E%3D7.2.0-brightgreen">
<img src="https://img.shields.io/badge/license-apache%202-blue">
</p>
官方网站：<a href="https://www.chengyao.xyz">https://www.chengyao.xyz</a>

Max-Database使用文档

> Max-Database 旨在轻量简单易用的数据库操作，用户不需要掌握太多SQL语言就可以实现增删改查

# 安装

该组件依赖于`MaxPHP`,`Max-Framework`,所以需要先安装`MaxPHP`

```shell
composer create-project max/max .
```

如果你已经安装，但是却没有安装`Max-Database`,需要使用下面的命令安装

```shell 
composer require max/database
```

> 安装完成后自动会将数据库配置文件`database.php`移动到框架的`config`目录下，如果没有移动则需要手动创建，配置文件实例如下

```php 
<?php

return [

    //默认数据库配置
    'default' => env('database.default', 'mysql'),

    'mysql' => [
        //如果有这项会优先使用DSN
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
        'prefix'  => ''
    ],
]
```

目前支持`MySQL`,`PostgreSQL`

# 使用

> 提供了下面两种操作方法，本质是相同的。

```php  
//门面
\Max\Facade\Db::name('users')->select()

//依赖注入
public function index(\Max\Database\Query $query) {
    $query->name('users')->select(); 
}
```

## 新增

向`users`表中新增一条数据

~~~
\Max\Facade\Db::name('users')->insert(['name' => 'username','age' => 28]);
~~~

## 删除

删除`users` 表中`id`为`1`的数据

```php
\Max\Facade\Db::name('users')->where(['id' => 1])->delete();
```

## 查询

### 条件构造

条件构造主要有以下几个方法：`table`,`name`,`fields`,`where`,`whereOr`,`whereIn`,`whereNotIn`,`whereLike`,`whereExists`,`whereBetween`
,`whereNull`,`whereNotNull`,`order`,`group`,`join`,`leftJoin`,`rightJoin`,`limit`。

#### table

```php
table(string $table)
```

如果有前缀则`$table`必须加上前缀

#### name

```php
name(string $table)
```

如果有前缀，则不需要加前缀

#### fields

```php
fields($fields)
```

设置要查询的字段，可以是索引数组，也可以是字符串，不使用该方法默认为`*`

#### order

```php
order(array $order)
```

```php
\Max\Facade\Db::name('users')->order(['id' => 'DESC','sex' => 'DESC'])->select();
```

最终的SQL可能是

```
SELECT * FROM users ORDER BY id DESC, sex DESC;
```

#### group

```php
group(array $group)
```

```php
\Max\Facade\Db::name('users')->group(['sex','id' => 'sex = 1'])->select();
```

最终的SQL可能是

```
SELECT * FROM users GROUP BY sex,id HAVING sex = 1;
```

#### limit

```php
limit(int $limit, int $offset = null)
```

```php
\Max\Facade\Db::name('users')->limit(1,3)->select();
\Max\Facade\Db::name('users')->limit(1)->select();
```

根据数据库不同最终的SQL可能是

```
SELECT * FROM users LIMIT 3,1;
SELECT * FROM users LIMIT 1;
```

也可能是

```
SELECT * FROM users LIMIT 1 OFFSET 3;
```

#### join

联表有提供了三种方式`innerJoin` `leftJoin` `rightJoin`

例如如下语句：

```php
\Max\Facade\Db::name('users')->join(['books' => 'books.user_id = users.id'])->select();
\Max\Facade\Db::name('users')->leftJoin(['books' => 'books.user_id = users.id'])->select();
\Max\Facade\Db::name('users')->rightJoin(['books' => 'books.user_id = users.id'])->select();
```

最终的SQL可能是

```
SELECT * FROM users INNER JOIN books on books.user_id = users.id;
SELECT * FROM users LEFT JOIN books on books.user_id = users.id;
SELECT * FROM users RIGHT JOIN books on books.user_id = users.id;
```

#### where

```php
where(array $where, string $operator = '=')
```

例如我有如下的查询

```php
\Max\Facade\Db::name('users')->where(['id' => 1, 'sex = 0'])->select();
\Max\Facade\Db::name('users')->where(['id' => 2], '>=')->select();
```

最终的SQL可能依次如下

```
SELECT * FROM users WHERE id = ? AND sex = 0;
SELECT * FROM users WHERE id >= ?;
```

可以看到`id = ?` 和 `sex = 0` 说明id这个条件可以经过预处理的，而条件数组的键为数字的却不会被处理。

#### whereLike

```php
whereLike(array $whereLike)
```

例如我有如下的查询

```php
\Max\Facade\Db::name('users')->whereLike(['username' => 1, 'sex = 0'])->select();
```

最终的SQL可能如下

```
SELECT * FROM users WHERE username LIKE ? AND sex = 0;
```

### 查询一条可以用`get`方法

> 查询users表中id为1的一条数据，通常要配合条件语句定位数据

```php 
\Max\Facade\Db::name('users')->where(['id' => 1])->get()
```

### 查询多条可用`select`方法

> 查询users表中id在1，2，3范围内的2条，其偏移量为3

~~~
\Max\Facade\Db::name('users')->field('id')->whereIn(['id' => [1,2,3]])->limit(2,3)->select();
~~~

查询到的是数据集对象，可以使用`toArray`或者`toJson`获取，例如

~~~
\Max\Facade\Db::name('users')->limit(1)->select()->toArray();
~~~

### 查询某一个值

查询`users`表中的`id`为`2`的`username`

```php 
\Max\Facade\Db::name('users')->where(['id' => 2])->value('username');
```

### 查询某一列值

查询`users`表中的`username`列

```php 
\Max\Facade\Db::name('users')->column('username');
```

### 总数查询

查询`users`表的总数据，返回`int`

```php 
\Max\Facade\Db::name('users')->count();
```

> `count()`方法可以传入一个参数即列名，不传默认为*

## 更新

将`id`大于`10`的`users`表中的数据中`sex`字段全部更新为`boy`

~~~
\Max\Facade\Db::name('users')->where('id > 10')->update(['sex' => 'boy']);
~~~

## 删除

~~~
\Max\Facade\Db::name('users')->where('id > 10')->delete();
~~~

删除id大于10的用户。

## 其他

替代地，可以使用`query`和`exec`方法来执行`SQL`。一般地，`query`对应查询，`exec`对应增删改

### query

```php
\Max\Facade\Db::query('SELECT * FROM users WHERE id > ?', [10], true);
```

第一个参数为`SQL`字符串，可以包含`?`或者`:id`类似的占位符，第二个参数对应要绑定到`SQL`上的参数，第三个为`true`会查询全部，否则只取出一条。

```php
\Max\Facade\Db::exec('DELETE FROM users WHERE id > ?', [10]);
```

上面的执行结果为删除`users`表中`id`大于`10`的所有数据

# 事务

```php
$res = \Max\Database\Db::transaction(function (Query $query, \PDO $pdo) {
    //$pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS,true); 可以自行设置需要的参数
    $deletedUsers = $query->name('users')->whereIn(['id' => [1,2,3]])->delete();
    if(0 == $deletedUsers){
        throw new \PDOException('没有用户被删除!');
    }
    return $query->name('books')->whereIn(['user_id' => [1,2,3]])->delete();
});
```

其中`transaction`接受一个闭包的参数，该回调可以传入两个参数，一个是当前的查询实例，另一个是`PDO`实例，可以看到这里执行了两条`SQL`
，当执行结果不满足时可以手动抛出`PDOException`异常来回滚事务，否则如果执行过程抛出异常也会自动回滚事务。执行没有错误结束后提交事务。该闭包需要返回执行的结果，返回的执行结果会被`transaction`方法返回。

# 调试SQL

当我们编写好代码之后往往需要查看最终执行的SQL，这时候`Max-Database` 提供了一个方法`getSQL()` 在最终执行前调用该方法，会将SQL打印出来例如：

```php
\Max\Database\Db::name('users')->where(['id' => 1], '>=')->getSQL()->select();
```

无论`SQL`执行是否出错都可以使用上面的方法。如果执行结果返回的是数据集，例如`get()`,`select()`方法，执行成功后也可以调用`getSQL()`方法来获取`SQL`，也可以使用`getBindParmas()`
方法获取绑定的参数。


> 注意：你可以自行安装`medoo`，`think-orm`等数据库操作类库或者使用自带的Db类,该Db类的操作方法大部分需要的是数组类型的参数。

# redis cache strategy(read through)


### 简介

    该项目是基于read through 缓存策略实现的相关接口，基于redis
    你要做的是提供缓存策略的Provider层，当前项目已经封装了一些常用的
    数据结构（基于redis的数据结构实现）
    在你的Provider层实现相应的接口
    即可有效的管理你的缓存系统

### 安装

```
composer require edv/cache
```


### 支持数据结构
* CacheMap（redis 原 hashes 结构）
* CacheList（redis 原 zset 结构）
* CacheString（redis 原 string 结构）
* CacheBitmap(redis 原 bitmap 结构)


### 推荐目录结构
```
    cache
        |--provider1
            |--class UserList extend AbstractProvider1
            ... 
        |--provider2
            |--class Bitmap1 extend AbstractProvider2
            ... 
        |--abstract class AbstractProvider1 extend CacheList...(实现统一配置)
        |--abstract class AbstractProvider2 extend CacheBitmap...(实现统一配置)
        ...
```

### 需要实现的方法

    redis 链接配置 config():array

    return [
        'host' => '',
        'port' => '',
        'password' => '',
        'database' => '',
    ];

    config 建议在provider抽象类中实现，将统一的redis链接抽象成基类

    数据填充（list/map） patch():array

    list
        return [1,2,3];

    map 
        return ['key'=>'val1','key2'=>'val2'];


    patch 在子类中实现，如果不需要初始化缓存数据，可 use EmptyPatch trait


### 缓存过期策略

    1 可以通过过期时间，子类实现expire方法，expire 可以返回具体的过期时间（eg:"2022-02-12 09:12:00"） 也可以返回时长（单位是秒）

    2 调用flush主动删除缓存

```
该项目开箱即用，如果还有疑问可参考test.php中的相关案例。

 嗯...
```




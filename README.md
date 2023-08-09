<p align="center">
    <a href="https://github.com/yii1tech" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/134691944" height="100px">
    </a>
    <h1 align="center">Yii1 Tag Aware Cache Extension</h1>
    <br>
</p>

This extension provides tag aware cache for Yii1.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/yii1tech/tagged-cache.svg)](https://packagist.org/packages/yii1tech/tagged-cache)
[![Total Downloads](https://img.shields.io/packagist/dt/yii1tech/tagged-cache.svg)](https://packagist.org/packages/yii1tech/tagged-cache)
[![Build Status](https://github.com/yii1tech/tagged-cache/workflows/build/badge.svg)](https://github.com/yii1tech/tagged-cache/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii1tech/tagged-cache
```

or add

```json
"yii1tech/tagged-cache": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension provides tag aware cache for Yii1. Tags allow you to organize cache items into groups, each of which can be cleared separately.
This extension introduces `\yii1tech\cache\tagged\TagAwareCacheContract` interface, which extends Yii standard `\ICache`, adding extra parameter
for the tags specification on saving data to the cache.

Application configuration example:

```php
<?php

return [
    'components' => [
        'cache' => [
            'class' => \yii1tech\cache\tagged\MemCache::class, // implements `\yii1tech\cache\tagged\TagAwareCacheContract`
            'servers' => [
                [
                    'host' => 'memcache.server',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```

You may pass list of cache item tags as an extra argument to methods `get()` and `add()`.
For example:

```php
<?php

$cacheKey = 'example-cache-key';
$value = Yii::app()->cache->get($cacheKey);
if ($value === false) {
    $value = Yii::app()->db->createCommand('SELECT ...')->query(); // some heave SQL query.
    
    Yii::app()->cache->set(
        $cacheKey, // cache key
        $value, // value to be cached
        3600, // cache expiration
        null, // dependency, empty in our case
        ['database', 'main'] // list of cache tags.
    );
}
```

You can clear all cache items associated with the specific tags using `\yii1tech\cache\tagged\TagAwareCacheContract::invalidateTags()` method.
For example:

```php
<?php

Yii::app()->cache->invalidateTags(['database']);
```

This extension provides several built-in cache drivers, which supports tags:

- [\yii1tech\cache\tagged\MemCache](src/MemCache.php)
- [\yii1tech\cache\tagged\DbCache](src/DbCache.php)
- [\yii1tech\cache\tagged\ArrayCache](src/ArrayCache.php)

Please refer to the particular storage class for more details.

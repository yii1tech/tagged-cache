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

This extension provides tag aware cache for Yii1.

Supported drivers:

- [Memcached](src/MemCache.php)
- [DB](src/DbCache.php)
- [Array](src/ArrayCache.php)
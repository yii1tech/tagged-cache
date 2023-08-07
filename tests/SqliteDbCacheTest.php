<?php

namespace yii1tech\cache\tagged\test;

use Yii;
use yii1tech\cache\tagged\DbCache;
use yii1tech\cache\tagged\TagAwareCacheContract;

class SqliteDbCacheTest extends AbstractCacheTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createCache(): TagAwareCacheContract
    {
        $cache = new DbCache();
        $cache->autoCreateCacheTable = true;

        $cache->setDbConnection(Yii::createComponent([
            'class' => \CDbConnection::class,
            'connectionString' => 'sqlite::memory:',
        ]));

        $cache->init();

        return $cache;
    }
}
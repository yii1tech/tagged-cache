<?php

namespace yii1tech\cache\tagged\test;

use yii1tech\cache\tagged\MemCache;
use yii1tech\cache\tagged\TagAwareCacheContract;

/**
 * Run locally in Docker:
 *
 * ```
 * MEMCACHE_HOST=memcached phpunit
 * ```
 */
class MemCacheTest extends AbstractCacheTestCase
{
    /**
     * @var string test memcache server host
     */
    protected $memcacheHost = '127.0.0.1';
    /**
     * @var string test memcache server port
     */
    protected $memcachePort = 11211;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('Memcached extensions is required.');
        }

        if (isset($_SERVER['MEMCACHE_HOST'])) {
            $this->memcacheHost = $_SERVER['MEMCACHE_HOST'];
        }
        if (isset($_SERVER['MEMCACHE_PORT'])) {
            $this->memcachePort = $_SERVER['MEMCACHE_PORT'];
        }

        // check whether memcached is running and skip tests if not.
        if (!@stream_socket_client($this->memcacheHost . ':' . $this->memcachePort, $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('No memcached server running at ' . $this->memcacheHost . ':' . $this->memcachePort . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createCache(): TagAwareCacheContract
    {
        $cache = new MemCache();
        $cache->setServers([
            [
                'host' => $this->memcacheHost,
                'port' => $this->memcachePort,
                'weight' => 100,
            ],
        ]);
        $cache->init();

        return $cache;
    }
}
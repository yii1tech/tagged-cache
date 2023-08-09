<?php

namespace yii1tech\cache\tagged\test;

use yii1tech\cache\tagged\DbCache;
use yii1tech\cache\tagged\TagAwareCacheContract;

/**
 * Run locally in Docker:
 *
 * ```
 * MYSQL_HOST=postgres phpunit
 * ```
 */
class PgsqlDbCacheTest extends AbstractCacheTestCase
{
    /**
     * @var string test PostgreSQL server host
     */
    protected $pgsqlHost = '127.0.0.1';
    /**
     * @var string test PostgreSQL server port
     */
    protected $pgsqlPort = 5432;
    /**
     * @var string test PostgreSQL database name
     */
    protected $pgsqlDatabase = 'yiitest';
    /**
     * @var string test PostgreSQL username
     */
    protected $pgsqlUsername = 'yiitest';
    /**
     * @var string test PostgreSQL password
     */
    protected $pgsqlPassword = 'secret';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (!extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped('PDO PostgreSQL extensions is required.');
        }

        if (isset($_SERVER['POSTGRES_HOST'])) {
            $this->pgsqlHost = $_SERVER['POSTGRES_HOST'];
        }
        if (isset($_SERVER['POSTGRES_PORT'])) {
            $this->pgsqlPort = $_SERVER['POSTGRES_PORT'];
        }
        if (isset($_SERVER['POSTGRES_DATABASE'])) {
            $this->pgsqlDatabase = $_SERVER['POSTGRES_DATABASE'];
        }
        if (isset($_SERVER['POSTGRES_USERNAME'])) {
            $this->pgsqlUsername = $_SERVER['POSTGRES_USERNAME'];
        }
        if (isset($_SERVER['POSTGRES_PASSWORD'])) {
            $this->pgsqlPassword = $_SERVER['POSTGRES_PASSWORD'];
        }

        // check whether MySQL is running and skip tests if not:
        if (!@stream_socket_client($this->pgsqlHost . ':' . $this->pgsqlPort, $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('No PostgreSQL server running at ' . $this->pgsqlHost . ':' . $this->pgsqlPort . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function createCache(): TagAwareCacheContract
    {
        $cache = new DbCache();
        $cache->autoCreateCacheTable = true;

        $cache->setDbConnection(\Yii::createComponent([
            'class' => \CDbConnection::class,
            'connectionString' => "pgsql:host={$this->pgsqlHost};port={$this->pgsqlPort};dbname={$this->pgsqlDatabase};",
            'username' => $this->pgsqlUsername,
            'password' => $this->pgsqlPassword,
        ]));

        $cache->init();

        return $cache;
    }
}
<?php

namespace yii1tech\cache\tagged\test;

use yii1tech\cache\tagged\DbCache;
use yii1tech\cache\tagged\TagAwareCacheContract;

/**
 * Run locally in Docker:
 *
 * ```
 * MYSQL_HOST=mysql phpunit
 * ```
 */
class MysqlDbCacheTest extends AbstractCacheTestCase
{
    /**
     * @var string test MySQL server host
     */
    protected $mysqlHost = '127.0.0.1';
    /**
     * @var string test MySQL server port
     */
    protected $mysqlPort = 3306;
    /**
     * @var string test MySQL database name
     */
    protected $mysqlDatabase = 'yiitest';
    /**
     * @var string test MySQL username
     */
    protected $mysqlUsername = 'root';
    /**
     * @var string test MySQL password
     */
    protected $mysqlPassword = 'root';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('PDO MySQL extensions is required.');
        }

        if (isset($_SERVER['MYSQL_HOST'])) {
            $this->mysqlHost = $_SERVER['MYSQL_HOST'];
        }
        if (isset($_SERVER['MYSQL_PORT'])) {
            $this->mysqlPort = $_SERVER['MYSQL_PORT'];
        }
        if (isset($_SERVER['MYSQL_DATABASE'])) {
            $this->mysqlDatabase = $_SERVER['MYSQL_DATABASE'];
        }
        if (isset($_SERVER['MYSQL_USERNAME'])) {
            $this->mysqlUsername = $_SERVER['MYSQL_USERNAME'];
        }
        if (isset($_SERVER['MYSQL_PASSWORD'])) {
            $this->mysqlPassword = $_SERVER['MYSQL_PASSWORD'];
        }

        // check whether MySQL is running and skip tests if not:
        if (!@stream_socket_client($this->mysqlHost . ':' . $this->mysqlPort, $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('No MySQL server running at ' . $this->mysqlHost . ':' . $this->mysqlPort . ' : ' . $errorNumber . ' - ' . $errorDescription);
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
            'connectionString' => "mysql:host={$this->mysqlHost};port={$this->mysqlPort};dbname={$this->mysqlDatabase}",
            'username' => $this->mysqlUsername,
            'password' => $this->mysqlPassword,
        ]));

        $cache->init();

        return $cache;
    }
}
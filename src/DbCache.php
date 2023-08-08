<?php

namespace yii1tech\cache\tagged;

use PDO;

/**
 * DbCache is tag aware version of standard {@see \CDbCache}.
 *
 * It saves tags in the same row with cache item into a JSON field.
 * Only following drivers are supported:
 *
 * - 'sqlite'
 * - 'mysql'
 * - 'pgsql'
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'db' => [
 *             'class' => \CDbConnection::class,
 *             'connectionString' => 'sqlite:path/to/dbfile',
 *         ],
 *         'cache' => [
 *             'class' => \yii1tech\cache\tagged\DbCache::class,
 *             'connectionID' => 'db',
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DbCache extends \CDbCache implements TagAwareCacheContract
{
    /**
     * @var string[] list of tags to be applied for new item.
     */
    protected $currentItemTags = [];
    /**
     * @var bool whether GC has been performed.
     */
    private $_gced = false;

    /**
     * {@inheritdoc}
     */
    public function set($id, $value, $expire = 0, $dependency = null, array $tags = [])
    {
        $this->currentItemTags = $tags;

        $result = parent::set($id, $value, $expire, $dependency);

        $this->currentItemTags = [];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function add($id, $value, $expire = 0, $dependency = null, array $tags = [])
    {
        $this->currentItemTags = $tags;

        $result = parent::add($id, $value, $expire, $dependency);

        $this->currentItemTags = [];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }

        $sql = "DELETE FROM {$this->cacheTableName} WHERE tags IS NOT NULL AND ";

        $params = [];
        foreach ($tags as $tag) {
            $params[':tag' . count($params)] = $tag;
        }

        $db = $this->getDbConnection();
        $driverName = $db->getDriverName();

        if ($driverName === 'sqlite') {
            $where = $this->createTagsConditionSqlite($params);
        } elseif ($driverName === 'mysql') {
            $where = $this->createTagsConditionMysql($params);
        } elseif ($driverName === 'pgsql') {
            $where = $this->createTagsConditionPgsql($params);
        } else {
            throw new \LogicException("Unsupported Database driver: {$driverName}");
        }

        $sql .= $where;

        $db->createCommand($sql)->execute($params);

        return true;
    }

    /**
     * Creates tags search condition for 'sqlite' driver.
     *
     * @param array<string, string> $tagParams tag parameters to be bound with SQL query.
     * @return string SQL where condition part.
     */
    protected function createTagsConditionSqlite(array $tagParams): string
    {
        $inTagsSql = implode(',', array_keys($tagParams));

        return <<<"SQL"
EXISTS (
    SELECT * 
    FROM json_each(json_extract(tags,'$')) 
    WHERE json_each.value IN ($inTagsSql)
)
SQL;
    }

    /**
     * Creates tags search condition for 'mysql' driver.
     *
     * @param array<string, string> $tagParams tag parameters to be bound with SQL query.
     * @return string SQL where condition part.
     */
    protected function createTagsConditionMysql(array $tagParams): string
    {
        $whereParts = [];
        foreach ($tagParams as $name => $value) {
            $whereParts[] = "JSON_CONTAINS(`tags`, CONCAT('\"', {$name}, '\"'), '$')";
        }

        return '(' . implode(' OR ', $whereParts) . ')';
    }

    /**
     * Creates tags search condition for 'pgsql' driver.
     *
     * @param array<string, string> $tagParams tag parameters to be bound with SQL query.
     * @return string SQL where condition part.
     */
    protected function createTagsConditionPgsql(array $tagParams): string
    {
        $whereParts = [];
        foreach ($tagParams as $name => $value) {
            $whereParts[] = "(tags)::jsonb ? {$name}";
        }

        return '(' . implode(' OR ', $whereParts) . ')';
    }

    /**
     * {@inheritdoc}
     */
    protected function createCacheTable($db,$tableName)
    {
        $driver = $db->getDriverName();

        $blob = 'BLOB';
        $json = 'JSON';

        if ($driver==='mysql') {
            $blob = 'LONGBLOB';
        } elseif($driver==='pgsql') {
            $blob = 'BYTEA';
            $json = 'JSONB';
        }

        $sql = <<<"SQL"
CREATE TABLE $tableName
(
    id CHAR(128) PRIMARY KEY,
    expire INTEGER,
    value $blob,
    tags $json
)
SQL;
        $db->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    protected function addValue($key, $value, $expire)
    {
        if (!$this->_gced && mt_rand(0, 1000000) < $this->getGCProbability()) {
            $this->gc();
            $this->_gced = true;
        }

        if ($expire > 0) {
            $expire += time();
        } else {
            $expire = 0;
        }

        $tags = empty($this->currentItemTags) ? null : json_encode($this->currentItemTags);

        $sql = "INSERT INTO {$this->cacheTableName} (id,expire,value,tags) VALUES ('$key',$expire,:value,:tags)";
        try {
            $command = $this->getDbConnection()->createCommand($sql);
            $command->bindValue(':value', $value, PDO::PARAM_LOB);
            $command->bindValue(':tags', $tags);

            return $command->execute() > 0;
        } catch(\Exception $e) {
            return false;
        }
    }
}
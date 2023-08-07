<?php

namespace yii1tech\cache\tagged;

use CMemCache;
use Memcached;

/**
 * MemCache is tag aware version of standard {@see \CMemCache}.
 *
 * It saves tagged cache item keys into separated entities inside MemCache.
 *
 * > Note: unlike {@see \CMemCache} this class does not support "memcache" PHP extension and requires usage of "memcached" instead.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'cache' => [
 *             'class' => \yii1tech\cache\tagged\MemCache::class,
 *             'servers' => [
 *                 [
 *                     'host' => 'server1',
 *                     'port' => 11211,
 *                     'weight' => 60,
 *                 ],
 *                 [
 *                     'host' => 'server2',
 *                     'port' => 11211,
 *                     'weight' => 40,
 *                 ],
 *             ],
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
class MemCache extends CMemCache implements TagAwareCacheContract
{
    /**
     * {@inheritdoc}
     */
    public $useMemcached = true;
    /**
     * @var string key prefix for the tag data entries.
     */
    public $tagKeyPrefix = '__tag_';

    /**
     * {@inheritdoc}
     */
    public function set($id, $value, $expire = 0, $dependency = null, array $tags = [])
    {
        if (!parent::set($id, $value, $expire, $dependency)) {
            return false;
        }

        $result = true;
        foreach ($tags as $tag) {
            if (!$this->tagKey($tag, $id)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function add($id, $value, $expire = 0, $dependency = null, array $tags = [])
    {
        if (!parent::add($id, $value, $expire, $dependency)) {
            return false;
        }

        $result = true;
        foreach ($tags as $tag) {
            if (!$this->tagKey($tag, $id)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags): bool
    {
        $result = true;
        foreach ($tags as $tag) {
            $tagKey = $this->generateUniqueKey($this->tagKeyPrefix . $tag);

            $value = $this->getValue($tagKey);
            if (!empty($value)) {
                $keys = json_decode($value);
                if (!empty($keys)) {
                    $deleteResults = $this->getMemCache()->deleteMulti($keys);
                    if (in_array(false, $deleteResults, true)) {
                        $result = false;
                    }
                }
            }

            if (!$this->deleteValue($tagKey)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Adds given tag to specified cache key.
     *
     * @param string $tag tag name.
     * @param string $key cache key to be tagged.
     * @return bool whether operation is successful.
     */
    protected function tagKey(string $tag, string $key): bool
    {
        $tagKey = $this->generateUniqueKey($this->tagKeyPrefix . $tag);
        $key = $this->generateUniqueKey($key);

        $memcache = $this->getMemCache();

        while (true) {
            $data = $memcache->get($tagKey, null, Memcached::GET_EXTENDED);
            if ($data === false || $memcache->getResultCode() === Memcached::RES_NOTFOUND) {
                $value = json_encode([$key]);

                $memcache->add($tagKey, $value);
            } else {
                $value = $data['value'];
                $casToken = $data['cas'];

                $existingKeys = json_decode($value);

                if (empty($existingKeys)) {
                    $value = json_encode([$key]);
                } else {
                    $value = json_encode(array_merge($existingKeys, [$key]));
                }

                $memcache->cas($casToken, $tagKey, $value);
            }

            $resultCode = $memcache->getResultCode();

            if ($resultCode === Memcached::RES_DATA_EXISTS) {
                continue;
            }

            if (in_array($resultCode, [Memcached::RES_SUCCESS, Memcached::RES_END, Memcached::RES_STORED], true)) {
                return true;
            }

            return false;
        }
    }
}
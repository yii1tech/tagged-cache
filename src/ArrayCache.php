<?php

namespace yii1tech\cache\tagged;

use CCache;

/**
 * ArrayCache uses in memory array to store cached data.
 *
 * It does not provide persistent cache storage and ignores cache expiration.
 *
 * This class can be used while writing unit tests.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ArrayCache extends CCache implements TagAwareCacheContract
{
    /**
     * @var array<string, mixed> cached data.
     */
    private $data = [];
    /**
     * @var array<string, array> cached tags.
     */
    private $tags = [];

    /**
     * {@inheritdoc}
     */
    public function set($id, $value, $expire = 0, $dependency = null, array $tags = [])
    {
        if (!parent::set($id, $value, $expire, $dependency)) {
            return false;
        }

        foreach ($tags as $tag) {
            $this->tags[$tag][] = $id;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function add($id, $value, $expire = 0, $dependency = null, array $tags = [])
    {
        if (!parent::add($id, $value, $expire, $dependency)) {
            return false;
        }

        foreach ($tags as $tag) {
            $this->tags[$tag][] = $id;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags): bool
    {
        $result = true;

        foreach ($tags as $tag) {
            if (!isset($this->tags[$tag])) {
                continue;
            }

            foreach ($this->tags[$tag] as $id) {
                if (!$this->delete($id)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($key)
    {
        return $this->data[$key] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValues($keys)
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->data[$key] ?? false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $expire)
    {
        $this->data[$key] = $value;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function addValue($key, $value, $expire)
    {
        if (isset($this->data[$key])) {
            return false;
        }

        $this->data[$key] = $value;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteValue($key)
    {
        unset($this->data[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function flushValues()
    {
        $this->data = [];
        $this->tags = [];

        return true;
    }
}
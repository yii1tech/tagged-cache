<?php

namespace yii1tech\cache\tagged;

/**
 * TagAwareCacheContract is the interface that must be implemented by tag aware cache components.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface TagAwareCacheContract extends \ICache
{
    /**
     * {@inheritdoc}
     * @param string[] $tags tags, which should be associated with the cached value.
     */
    public function set($id, $value, $expire = 0, $dependency = null, array $tags = []);

    /**
     * {@inheritdoc}
     * @param string[] $tags tags, which should be associated with the cached value.
     */
    public function add($id, $value, $expire = 0, $dependency = null, array $tags = []);

    /**
     * Deletes cached entries, associated with given tags.
     *
     * @param string[] $tags tags, which associated with items should be deleted.
     * @return bool whether cache entries have been successfully deleted or not.
     */
    public function invalidateTags(array $tags): bool;
}
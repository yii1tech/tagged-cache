<?php

namespace yii1tech\cache\tagged\test;

use yii1tech\cache\tagged\TagAwareCacheContract;

abstract class AbstractCacheTestCase extends TestCase
{
    /**
     * Creates cache component to be tested.
     *
     * @return \yii1tech\cache\tagged\TagAwareCacheContract cache component instance.
     */
    abstract protected function createCache(): TagAwareCacheContract;

    public function testInvalidateTags(): void
    {
        $cache = $this->createCache();

        $cache->flush();

        $cache->set('test-1-1', 'value-1-1', 0, null, ['tag-1']);
        $cache->set('test-1-2', 'value-1-2', 0, null, ['tag-1']);
        $cache->set('test-2-1', 'value-2-1', 0, null, ['tag-2']);
        $cache->set('test-2-2', 'value-2-2', 0, null, ['tag-2']);

        $cache->invalidateTags(['tag-1']);

        $this->assertEmpty($cache->get('test-1-1'));
        $this->assertEmpty($cache->get('test-1-2'));
        $this->assertSame('value-2-1', $cache->get('test-2-1'));
        $this->assertSame('value-2-2', $cache->get('test-2-2'));
    }
}
<?php

namespace yii1tech\cache\tagged\test;

use yii1tech\cache\tagged\ArrayCache;

class ArrayCacheTest extends TestCase
{
    public function testGet(): void
    {
        $cache = new ArrayCache();

        $key = 'test';
        $value = 'test-value';

        $this->assertEmpty($cache->get($key));

        $this->assertTrue($cache->set($key, $value));
        $this->assertSame($value, $cache->get($key));
    }

    /**
     * @depends testGet
     */
    public function testGetBatch(): void
    {
        $cache = new ArrayCache();

        $key = 'test';
        $value = 'test-value';

        $values = $cache->mget([$key]);

        $this->assertArrayHasKey($key, $values);
        $this->assertEmpty($values[$key]);

        $cache->set($key, $value);

        $values = $cache->mget([$key]);

        $this->assertArrayHasKey($key, $values);
        $this->assertSame($value, $values[$key]);
    }

    /**
     * @depends testGet
     */
    public function testAdd(): void
    {
        $cache = new ArrayCache();

        $key = 'test';
        $value = 'test-value';

        $this->assertTrue($cache->add($key, $value));
        $this->assertSame($value, $cache->get($key));

        $this->assertFalse($cache->add($key, 'another-value'));
        $this->assertSame($value, $cache->get($key));
    }

    /**
     * @depends testGet
     */
    public function testDelete(): void
    {
        $cache = new ArrayCache();

        $key = 'test';
        $value = 'test-value';

        $cache->set($key, $value);

        $this->assertTrue($cache->delete($key));
        $this->assertEmpty($cache->get($key));
    }

    /**
     * @depends testGet
     */
    public function testFlush(): void
    {
        $cache = new ArrayCache();

        $key = 'test';
        $value = 'test-value';

        $cache->set($key, $value);

        $this->assertTrue($cache->flush());
        $this->assertEmpty($cache->get($key));
    }

    /**
     * @depends testGet
     */
    public function testInvalidateTags(): void
    {
        $cache = new ArrayCache();

        $cache->set('test-1', 'value-1', 0, null, ['tag-1']);
        $cache->set('test-2', 'value-2', 0, null, ['tag-2']);

        $cache->invalidateTags(['tag-1']);

        $this->assertEmpty($cache->get('test-1'));
        $this->assertSame('value-2', $cache->get('test-2'));
    }
}
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject\Test\Unit;

use Magento\Framework\DataObject\Cache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    /**
     * @var Cache
     */
    protected $cache;

    protected function setUp(): void
    {
        $this->cache = new Cache();
    }

    public function testSaveWhenArgumentIsNotObject()
    {
        $this->assertFalse($this->cache->save('string'));
    }

    public function testSaveWhenObjectAlreadyExistsInRegistry()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Object already exists in registry (#1). Old object class: stdClass');
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $newIdx = 'idx' . $hash;
        $this->assertEquals($newIdx, $this->cache->save($object, 'idx{hash}', ['tags_array']));
        $this->assertEquals([$newIdx => $object], $this->cache->findByClass('stdClass'));
        $this->assertEquals([$newIdx => $object], $this->cache->getAllObjects());
        $this->assertEquals($newIdx, $this->cache->find($object));
        $this->assertEquals([$newIdx => $object], $this->cache->findByIds([$newIdx]));
        $objectTwo = new \stdClass();
        $this->assertEquals('#1', $this->cache->save($objectTwo, null, 'tags_string'));
        $objectThree = new \stdClass();
        $this->cache->save($objectThree, '#1');
    }

    public function testSaveAndDeleteWhenHashAlreadyExist()
    {
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $this->assertEquals('idx' . $hash, $this->cache->save($object, 'idx{hash}'));
        $this->assertEquals('idx' . $hash, $this->cache->save($object));
        $this->assertTrue($this->cache->delete('idx' . $hash));
        $this->assertFalse($this->cache->delete('idx' . $hash));
    }

    public function testReferenceWhenReferenceAlreadyExist()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The reference already exists: refName. New index: idx, old index: idx');
        $refName = ['refName', 'refName'];
        $this->cache->reference($refName, 'idx');
    }

    public function testReferenceWhenReferenceEmpty()
    {
        $this->assertNull($this->cache->reference([], 'idx'));
    }

    public function testLoadWhenReferenceAndObjectAlreadyExists()
    {
        $idx = 'idx';
        $this->cache->reference('refName', $idx);
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $this->assertNull($this->cache->findByHash($hash));
        $this->cache->save($object, $idx);
        $this->assertEquals($object, $this->cache->load($idx));
        $this->assertTrue($this->cache->has($idx));
        $this->assertEquals($object, $this->cache->findByHash($hash));
        $this->assertEquals(['refName' => 'idx'], $this->cache->getAllReferences());
    }

    public function testLoad()
    {
        $this->assertEquals('default', $this->cache->load('idx', 'default'));
    }

    public function testDeleteWhenIdxIsObject()
    {
        $object = new \stdClass();
        $this->cache->save($object, 'idx{hash}');
        $this->assertFalse($this->cache->delete($object));
        $this->cache->save($object, false);
        $this->assertFalse($this->cache->delete($object));
    }

    public function testDeleteIfReferencesExists()
    {
        $this->cache->reference('refName', 'idx');
        $object = new \stdClass();
        $this->cache->save($object, 'idx');
        $this->assertTrue($this->cache->delete('idx'));
    }

    public function testDeleteByClass()
    {
        $object = new \stdClass();
        $this->cache->save($object, 'idx');
        $this->cache->deleteByClass('stdClass');
        $this->assertFalse($this->cache->find($object));
    }

    public function testDebug()
    {
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $newIdx = 'idx' . $hash;
        $this->assertEquals($newIdx, $this->cache->save($object, 'idx{hash}'));
        $this->cache->debug($newIdx);
        $this->assertArrayHasKey($newIdx, $this->cache->debugByIds($newIdx));
    }

    public function testGetAndDeleteTags()
    {
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $newIdx = 'idx' . $hash;
        $tags = ['tags_array' => [$newIdx => true]];
        $tagsByObj = [$newIdx => ['tags_array' => true]];
        $this->assertEquals($newIdx, $this->cache->save($object, 'idx{hash}', ['tags_array']));
        $this->assertEquals($tags, $this->cache->getAllTags());
        $this->assertEquals([$newIdx => $object], $this->cache->findByTags('tags_array'));
        $this->assertEquals($tagsByObj, $this->cache->getAllTagsByObject());
        $this->assertTrue($this->cache->deleteByTags('tags_array'));
    }

    public function testSinglton()
    {
        $this->assertEquals($this->cache, $this->cache->singleton());
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DataObject\Test\Unit;

class CacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Cache
     */
    protected $cache;

    protected function setUp()
    {
        $this->cache = new \Magento\Framework\DataObject\Cache();
    }

    public function testSaveWhenArgumentIsNotObject()
    {
        $this->assertSame(false, $this->cache->save('string'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Object already exists in registry (#1). Old object class: stdClass
     */
    public function testSaveWhenObjectAlreadyExistsInRegistry()
    {
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $newIdx = 'idx' . $hash;
        $this->assertSame($newIdx, $this->cache->save($object, 'idx{hash}', ['tags_array']));
        $this->assertSame([$newIdx => $object], $this->cache->findByClass('stdClass'));
        $this->assertSame([$newIdx => $object], $this->cache->getAllObjects());
        $this->assertSame($newIdx, $this->cache->find($object));
        $this->assertSame([$newIdx => $object], $this->cache->findByIds([$newIdx]));
        $objectTwo = new \stdClass();
        $this->assertSame('#1', $this->cache->save($objectTwo, null, 'tags_string'));
        $objectThree = new \stdClass();
        $this->cache->save($objectThree, '#1');
    }

    public function testSaveAndDeleteWhenHashAlreadyExist()
    {
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $this->assertSame('idx' . $hash, $this->cache->save($object, 'idx{hash}'));
        $this->assertSame('idx' . $hash, $this->cache->save($object));
        $this->assertTrue($this->cache->delete('idx' . $hash));
        $this->assertFalse($this->cache->delete('idx' . $hash));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The reference already exists: refName. New index: idx, old index: idx
     */
    public function testReferenceWhenReferenceAlreadyExist()
    {
        $refName = ['refName', 'refName'];
        $this->cache->reference($refName, 'idx');
    }

    public function testReferenceWhenReferenceEmpty()
    {
        $this->assertSame(null, $this->cache->reference([], 'idx'));
    }

    public function testLoadWhenReferenceAndObjectAlreadyExists()
    {
        $idx = 'idx';
        $this->cache->reference('refName', $idx);
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $this->assertSame(null, $this->cache->findByHash($hash));
        $this->cache->save($object, $idx);
        $this->assertSame($object, $this->cache->load($idx));
        $this->assertSame(true, $this->cache->has($idx));
        $this->assertSame($object, $this->cache->findByHash($hash));
        $this->assertSame(['refName' => 'idx'], $this->cache->getAllReferences());
    }

    public function testLoad()
    {
        $this->assertSame('default', $this->cache->load('idx', 'default'));
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
        $this->assertSame($newIdx, $this->cache->save($object, 'idx{hash}'));
        $this->cache->debug($newIdx);
        $this->assertTrue(array_key_exists($newIdx, $this->cache->debugByIds($newIdx)));
    }

    public function testGetAndDeleteTags()
    {
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $newIdx = 'idx' . $hash;
        $tags = ['tags_array' => [$newIdx => true]];
        $tagsByObj = [$newIdx => ['tags_array' => true]];
        $this->assertSame($newIdx, $this->cache->save($object, 'idx{hash}', ['tags_array']));
        $this->assertSame($tags, $this->cache->getAllTags());
        $this->assertSame([$newIdx => $object], $this->cache->findByTags('tags_array'));
        $this->assertSame($tagsByObj, $this->cache->getAllTagsByObject());
        $this->assertTrue($this->cache->deleteByTags('tags_array'));
    }

    public function testSinglton()
    {
        $this->assertSame($this->cache, $this->cache->singleton());
    }
}

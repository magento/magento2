<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Object;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Cache
     */
    protected $cache;

    protected function setUp()
    {
        $this->cache = new \Magento\Framework\Object\Cache();
    }

    public function testSaveWhenArgumentIsNotObject()
    {
        $this->assertEquals(false, $this->cache->save('string'));
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage Object already exists in registry (#1). Old object class: stdClass
     */
    public function testSaveWhenObjectAlreadyExistsInRegistry()
    {
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

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage The reference already exists: refName. New index: idx, old index: idx
     */
    public function testReferenceWhenReferenceAlreadyExist()
    {
        $refName = ['refName', 'refName'];
        $this->cache->reference($refName, 'idx');
    }

    public function testReferenceWhenReferenceEmpty()
    {
        $this->assertEquals(null, $this->cache->reference([], 'idx'));
    }

    public function testLoadWhenReferenceAndObjectAlreadyExists()
    {
        $idx = 'idx';
        $this->cache->reference('refName', $idx);
        $object = new \stdClass();
        $hash = spl_object_hash($object);
        $this->assertEquals(null, $this->cache->findByHash($hash));
        $this->cache->save($object, $idx);
        $this->assertEquals($object, $this->cache->load($idx));
        $this->assertEquals(true, $this->cache->has($idx));
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
        $this->assertTrue(array_key_exists($newIdx, $this->cache->debugByIds($newIdx)));
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

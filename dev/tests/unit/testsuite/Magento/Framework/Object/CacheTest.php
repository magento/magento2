<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->assertEquals($newIdx, $this->cache->save($object, 'idx{hash}', array('tags_array')));
        $this->assertEquals(array($newIdx => $object), $this->cache->findByClass('stdClass'));
        $this->assertEquals(array($newIdx => $object), $this->cache->getAllObjects());
        $this->assertEquals($newIdx, $this->cache->find($object));
        $this->assertEquals(array($newIdx => $object), $this->cache->findByIds(array($newIdx)));
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
        $refName = array('refName', 'refName');
        $this->cache->reference($refName, 'idx');
    }

    public function testReferenceWhenReferenceEmpty()
    {
        $this->assertEquals(null, $this->cache->reference(array(), 'idx'));
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
        $this->assertEquals(array('refName' => 'idx'), $this->cache->getAllReferences());
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
        $tags = array('tags_array' => array($newIdx => true));
        $tagsByObj = array($newIdx => array('tags_array' => true));
        $this->assertEquals($newIdx, $this->cache->save($object, 'idx{hash}', array('tags_array')));
        $this->assertEquals($tags, $this->cache->getAllTags());
        $this->assertEquals(array($newIdx => $object), $this->cache->findByTags('tags_array'));
        $this->assertEquals($tagsByObj, $this->cache->getAllTagsByObject());
        $this->assertTrue($this->cache->deleteByTags('tags_array'));
    }

    public function testSinglton()
    {
        $this->assertEquals($this->cache, $this->cache->singleton());
    }
}

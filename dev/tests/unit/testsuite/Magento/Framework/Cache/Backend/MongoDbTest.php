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
namespace Magento\Framework\Cache\Backend;

class MongoDbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Cache\Backend\MongoDb|null
     */
    protected $_model = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collection = null;

    protected function setUp()
    {
        $this->_collection = $this->getMock(
            'MongoCollection',
            array('find', 'findOne', 'distinct', 'save', 'update', 'remove', 'drop'),
            array(),
            '',
            false
        );
        $this->_model = $this->getMock(
            'Magento\Framework\Cache\Backend\MongoDb',
            array('_getCollection'),
            array(),
            '',
            false
        );
        $this->_model->expects($this->any())->method('_getCollection')->will($this->returnValue($this->_collection));
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_collection = null;
    }

    /**
     * @param array $ids
     * @param array $expected
     * @dataProvider getIdsDataProvider
     */
    public function testGetIds(array $ids, array $expected)
    {
        $result = new \ArrayIterator($ids);
        $this->_collection->expects($this->once())->method('find')->will($this->returnValue($result));
        $actual = $this->_model->getIds();
        $this->assertEquals($expected, $actual);
    }

    public function getIdsDataProvider()
    {
        return array(
            'empty db' => array(array(), array()),
            'multiple records' => array(array('id1' => 'id1', 'id2' => 'id2'), array('id1', 'id2'))
        );
    }

    /**
     * @param array $tags
     * @dataProvider getTagsDataProvider
     */
    public function testGetTags(array $tags)
    {
        $this->_collection->expects($this->once())->method('distinct')->with('tags')->will($this->returnValue($tags));
        $actual = $this->_model->getTags();
        $this->assertEquals($tags, $actual);
    }

    public function getTagsDataProvider()
    {
        return array('no tags' => array(array()), 'multiple tags' => array(array('tag1', 'tag2')));
    }

    /**
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsNotMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingAnyTags
     * @dataProvider getIdsMatchingTagsDataProvider
     */
    public function testGetIdsMatchingTags($method, $tags, $expectedInput)
    {
        $expectedOutput = new \ArrayIterator(array('test1' => 'test1', 'test2' => 'test2'));
        $expectedIds = array('test1', 'test2');
        $this->_collection->expects(
            $this->once()
        )->method(
            'find'
        )->with(
            $expectedInput
        )->will(
            $this->returnValue($expectedOutput)
        );
        $actualIds = $this->_model->{$method}($tags);
        $this->assertEquals($expectedIds, $actualIds);
    }

    public function getIdsMatchingTagsDataProvider()
    {
        return array(
            'getIdsMatchingTags() - one tag' => array(
                'getIdsMatchingTags',
                array('tag1'),
                array('$and' => array(array('tags' => 'tag1')))
            ),
            'getIdsMatchingTags() - multiple tags' => array(
                'getIdsMatchingTags',
                array('tag1', 'tag2'),
                array('$and' => array(array('tags' => 'tag1'), array('tags' => 'tag2')))
            ),
            'getIdsNotMatchingTags() - one tag' => array(
                'getIdsNotMatchingTags',
                array('tag1'),
                array('$nor' => array(array('tags' => 'tag1')))
            ),
            'getIdsNotMatchingTags() - multiple tags' => array(
                'getIdsNotMatchingTags',
                array('tag1', 'tag2'),
                array('$nor' => array(array('tags' => 'tag1'), array('tags' => 'tag2')))
            ),
            'getIdsMatchingAnyTags() - one tag' => array(
                'getIdsMatchingAnyTags',
                array('tag1'),
                array('$or' => array(array('tags' => 'tag1')))
            ),
            'getIdsMatchingAnyTags() - multiple tags' => array(
                'getIdsMatchingAnyTags',
                array('tag1', 'tag2'),
                array('$or' => array(array('tags' => 'tag1'), array('tags' => 'tag2')))
            )
        );
    }

    /**
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsNotMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingAnyTags
     */
    public function testGetIdsMatchingTagsNoInputTags()
    {
        $this->_collection->expects($this->never())->method('find');
        $this->assertEquals(array(), $this->_model->getIdsMatchingTags(array()));
        $this->assertEquals(array(), $this->_model->getIdsNotMatchingTags(array()));
        $this->assertEquals(array(), $this->_model->getIdsMatchingAnyTags(array()));
    }

    public function testGetFillingPercentage()
    {
        $actual = $this->_model->getFillingPercentage();
        $this->assertGreaterThan(0, $actual);
        $this->assertLessThan(100, $actual);
    }

    /**
     * @param mixed $cacheId
     * @param array $expectedInput
     * @param array|null $mongoOutput
     * @param array|bool $expected
     * @dataProvider getMetadatasDataProvider
     */
    public function testGetMetadatas($cacheId, $expectedInput, $mongoOutput, $expected)
    {
        $this->_collection->expects(
            $this->once()
        )->method(
            'findOne'
        )->with(
            $expectedInput
        )->will(
            $this->returnValue($mongoOutput)
        );
        $actual = $this->_model->getMetadatas($cacheId);
        $this->assertEquals($expected, $actual);
    }

    public function getMetadatasDataProvider()
    {
        $time = time();
        return array(
            'existing record' => array(
                'test_id',
                array('_id' => 'test_id'),
                array('_id' => 'test_id', 'data' => 'data', 'tags' => array(), 'expire' => $time, 'mtime' => $time),
                array('_id' => 'test_id', 'data' => 'data', 'tags' => array(), 'expire' => $time, 'mtime' => $time)
            ),
            'non-existing record' => array('test_id', array('_id' => 'test_id'), null, false),
            'non-string id' => array(
                10,
                array('_id' => '10'),
                array('_id' => 'test_id', 'data' => 'data', 'tags' => array(), 'expire' => $time, 'mtime' => $time),
                array('_id' => 'test_id', 'data' => 'data', 'tags' => array(), 'expire' => $time, 'mtime' => $time)
            )
        );
    }

    public function testTouch()
    {
        $cacheId = 'test';
        $this->_collection->expects($this->once())->method('update')->with($this->arrayHasKey('_id'));
        $this->_model->touch($cacheId, 100);
    }

    public function testGetCapabilities()
    {
        $capabilities = $this->_model->getCapabilities();
        $this->assertArrayHasKey('automatic_cleaning', $capabilities);
        $this->assertArrayHasKey('tags', $capabilities);
        $this->assertArrayHasKey('expired_read', $capabilities);
        $this->assertArrayHasKey('priority', $capabilities);
        $this->assertArrayHasKey('infinite_lifetime', $capabilities);
        $this->assertArrayHasKey('get_list', $capabilities);
    }

    /**
     * @param bool $doNotTestValidity
     * @dataProvider loadDataProvider
     */
    public function testLoad($doNotTestValidity)
    {
        include_once __DIR__ . '/_files/MongoBinData.txt';

        $cacheId = 'test_id';
        $expected = 'test_data';
        $validityCondition = $this->arrayHasKey('$or');
        if ($doNotTestValidity) {
            $validityCondition = $this->logicalNot($validityCondition);
        }
        $binData = new \MongoBinData($expected, \MongoBinData::BYTE_ARRAY);
        $binData->bin = $expected;
        $this->_collection->expects(
            $this->once()
        )->method(
            'findOne'
        )->with(
            $this->logicalAnd($this->arrayHasKey('_id'), $validityCondition)
        )->will(
            $this->returnValue(array('data' => $binData))
        );
        $actual = $this->_model->load($cacheId, $doNotTestValidity);
        $this->assertSame($expected, $actual);
    }

    public function loadDataProvider()
    {
        return array('test validity' => array(false), 'do not test validity' => array(true));
    }

    public function testLoadNoRecord()
    {
        $this->_collection->expects($this->once())->method('findOne')->will($this->returnValue(null));
        $this->assertFalse($this->_model->load('test_id'));
    }

    public function testTest()
    {
        $cacheId = 'test_id';
        $time = time();
        $this->_collection->expects(
            $this->once()
        )->method(
            'findOne'
        )->with(
            $this->logicalAnd($this->arrayHasKey('_id'), $this->contains($cacheId))
        )->will(
            $this->returnValue(array('mtime' => $time))
        );
        $this->assertSame($time, $this->_model->test($cacheId));
    }

    public function testTestNotFound()
    {
        $this->_collection->expects($this->once())->method('findOne')->will($this->returnValue(null));
        $this->assertFalse($this->_model->test('test_id'));
    }

    public function testSave()
    {
        include_once __DIR__ . '/_files/MongoBinData.txt';

        $inputAssertion = $this->logicalAnd(
            $this->arrayHasKey('_id'),
            $this->arrayHasKey('data'),
            $this->arrayHasKey('tags'),
            $this->arrayHasKey('mtime'),
            $this->arrayHasKey('expire')
        );
        $this->_collection->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            $inputAssertion
        )->will(
            $this->returnValue(true)
        );

        $this->assertTrue($this->_model->save('test data', 'test_id', array('tag1', 'tag2'), 100));
    }

    public function testRemove()
    {
        $cacheId = 'test';
        $this->_collection->expects(
            $this->once()
        )->method(
            'remove'
        )->with(
            array('_id' => $cacheId)
        )->will(
            $this->returnValue(true)
        );
        $this->assertTrue($this->_model->remove($cacheId));
    }

    /**
     * @param string $mode
     * @param array $tags
     * @param array $expectedQuery
     * @dataProvider cleanDataProvider
     */
    public function testClean($mode, $tags, $expectedQuery)
    {
        $this->_collection->expects($this->once())->method('remove')->with($expectedQuery);

        $this->_model->clean($mode, $tags);
    }

    public function cleanDataProvider()
    {
        return array(
            'clean expired' => array(\Zend_Cache::CLEANING_MODE_OLD, array(), $this->arrayHasKey('expire')),
            'clean cache matching all tags (string)' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                'tag1',
                array('$and' => array(array('tags' => 'tag1')))
            ),
            'clean cache matching all tags (one tag)' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('tag1'),
                array('$and' => array(array('tags' => 'tag1')))
            ),
            'clean cache matching all tags (multiple tags)' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('tag1', 'tag2'),
                array('$and' => array(array('tags' => 'tag1'), array('tags' => 'tag2')))
            ),
            'clean cache not matching tags (string)' => array(
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                'tag1',
                array('$nor' => array(array('tags' => 'tag1')))
            ),
            'clean cache not matching tags (one tag)' => array(
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                array('tag1'),
                array('$nor' => array(array('tags' => 'tag1')))
            ),
            'clean cache not matching tags (multiple tags)' => array(
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                array('tag1', 'tag2'),
                array('$nor' => array(array('tags' => 'tag1'), array('tags' => 'tag2')))
            ),
            'clean cache matching any tags (string)' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                'tag1',
                array('$or' => array(array('tags' => 'tag1')))
            ),
            'clean cache matching any tags (one tag)' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                array('tag1'),
                array('$or' => array(array('tags' => 'tag1')))
            ),
            'clean cache matching any tags (multiple tags)' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                array('tag1', 'tag2'),
                array('$or' => array(array('tags' => 'tag1'), array('tags' => 'tag2')))
            )
        );
    }

    public function cleanAll()
    {
        $this->_collection->expects($this->once())->method('drop')->will($this->returnValue(array('ok' => true)));
        $this->assertTrue($this->_model->clean(\Zend_Cache::CLEANING_MODE_ALL));
    }

    public function cleanNoTags()
    {
        $this->_collection->expects($this->never())->method('remove');
        $modes = array(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG
        );
        foreach ($modes as $mode) {
            $this->assertFalse($this->_model->clean($mode));
        }
    }

    /**
     * @expectedException \Zend_Cache_Exception
     * @expectedExceptionMessage Unsupported cleaning mode: invalid_mode
     */
    public function testCleanInvalidMode()
    {
        $this->_model->clean('invalid_mode');
    }
}

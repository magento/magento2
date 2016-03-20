<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit\Backend;

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
            ['find', 'findOne', 'distinct', 'save', 'update', 'remove', 'drop'],
            [],
            '',
            false
        );
        $this->_model = $this->getMock(
            'Magento\Framework\Cache\Backend\MongoDb',
            ['_getCollection'],
            [],
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
        return [
            'empty db' => [[], []],
            'multiple records' => [['id1' => 'id1', 'id2' => 'id2'], ['id1', 'id2']]
        ];
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
        return ['no tags' => [[]], 'multiple tags' => [['tag1', 'tag2']]];
    }

    /**
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsNotMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingAnyTags
     * @dataProvider getIdsMatchingTagsDataProvider
     */
    public function testGetIdsMatchingTags($method, $tags, $expectedInput)
    {
        $expectedOutput = new \ArrayIterator(['test1' => 'test1', 'test2' => 'test2']);
        $expectedIds = ['test1', 'test2'];
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
        return [
            'getIdsMatchingTags() - one tag' => [
                'getIdsMatchingTags',
                ['tag1'],
                ['$and' => [['tags' => 'tag1']]],
            ],
            'getIdsMatchingTags() - multiple tags' => [
                'getIdsMatchingTags',
                ['tag1', 'tag2'],
                ['$and' => [['tags' => 'tag1'], ['tags' => 'tag2']]],
            ],
            'getIdsNotMatchingTags() - one tag' => [
                'getIdsNotMatchingTags',
                ['tag1'],
                ['$nor' => [['tags' => 'tag1']]],
            ],
            'getIdsNotMatchingTags() - multiple tags' => [
                'getIdsNotMatchingTags',
                ['tag1', 'tag2'],
                ['$nor' => [['tags' => 'tag1'], ['tags' => 'tag2']]],
            ],
            'getIdsMatchingAnyTags() - one tag' => [
                'getIdsMatchingAnyTags',
                ['tag1'],
                ['$or' => [['tags' => 'tag1']]],
            ],
            'getIdsMatchingAnyTags() - multiple tags' => [
                'getIdsMatchingAnyTags',
                ['tag1', 'tag2'],
                ['$or' => [['tags' => 'tag1'], ['tags' => 'tag2']]],
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsNotMatchingTags
     * @covers \Magento\Framework\Cache\Backend\MongoDb::getIdsMatchingAnyTags
     */
    public function testGetIdsMatchingTagsNoInputTags()
    {
        $this->_collection->expects($this->never())->method('find');
        $this->assertEquals([], $this->_model->getIdsMatchingTags([]));
        $this->assertEquals([], $this->_model->getIdsNotMatchingTags([]));
        $this->assertEquals([], $this->_model->getIdsMatchingAnyTags([]));
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
        return [
            'existing record' => [
                'test_id',
                ['_id' => 'test_id'],
                ['_id' => 'test_id', 'data' => 'data', 'tags' => [], 'expire' => $time, 'mtime' => $time],
                ['_id' => 'test_id', 'data' => 'data', 'tags' => [], 'expire' => $time, 'mtime' => $time],
            ],
            'non-existing record' => ['test_id', ['_id' => 'test_id'], null, false],
            'non-string id' => [
                10,
                ['_id' => '10'],
                ['_id' => 'test_id', 'data' => 'data', 'tags' => [], 'expire' => $time, 'mtime' => $time],
                ['_id' => 'test_id', 'data' => 'data', 'tags' => [], 'expire' => $time, 'mtime' => $time],
            ]
        ];
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
            $this->returnValue(['data' => $binData])
        );
        $actual = $this->_model->load($cacheId, $doNotTestValidity);
        $this->assertSame($expected, $actual);
    }

    public function loadDataProvider()
    {
        return ['test validity' => [false], 'do not test validity' => [true]];
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
            $this->returnValue(['mtime' => $time])
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

        $this->assertTrue($this->_model->save('test data', 'test_id', ['tag1', 'tag2'], 100));
    }

    public function testRemove()
    {
        $cacheId = 'test';
        $this->_collection->expects(
            $this->once()
        )->method(
            'remove'
        )->with(
            ['_id' => $cacheId]
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
        return [
            'clean expired' => [\Zend_Cache::CLEANING_MODE_OLD, [], $this->arrayHasKey('expire')],
            'clean cache matching all tags (string)' => [
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                'tag1',
                ['$and' => [['tags' => 'tag1']]],
            ],
            'clean cache matching all tags (one tag)' => [
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                ['tag1'],
                ['$and' => [['tags' => 'tag1']]],
            ],
            'clean cache matching all tags (multiple tags)' => [
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                ['tag1', 'tag2'],
                ['$and' => [['tags' => 'tag1'], ['tags' => 'tag2']]],
            ],
            'clean cache not matching tags (string)' => [
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                'tag1',
                ['$nor' => [['tags' => 'tag1']]],
            ],
            'clean cache not matching tags (one tag)' => [
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                ['tag1'],
                ['$nor' => [['tags' => 'tag1']]],
            ],
            'clean cache not matching tags (multiple tags)' => [
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                ['tag1', 'tag2'],
                ['$nor' => [['tags' => 'tag1'], ['tags' => 'tag2']]],
            ],
            'clean cache matching any tags (string)' => [
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                'tag1',
                ['$or' => [['tags' => 'tag1']]],
            ],
            'clean cache matching any tags (one tag)' => [
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                ['tag1'],
                ['$or' => [['tags' => 'tag1']]],
            ],
            'clean cache matching any tags (multiple tags)' => [
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                ['tag1', 'tag2'],
                ['$or' => [['tags' => 'tag1'], ['tags' => 'tag2']]],
            ]
        ];
    }

    public function cleanAll()
    {
        $this->_collection->expects($this->once())->method('drop')->will($this->returnValue(['ok' => true]));
        $this->assertTrue($this->_model->clean(\Zend_Cache::CLEANING_MODE_ALL));
    }

    public function cleanNoTags()
    {
        $this->_collection->expects($this->never())->method('remove');
        $modes = [
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
        ];
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

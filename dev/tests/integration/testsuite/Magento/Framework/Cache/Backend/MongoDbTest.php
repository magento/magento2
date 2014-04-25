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
    protected $_connectionString;

    protected $_dbName = 'magento_integration_test';

    /**
     * @var \Magento\Framework\Cache\Backend\MongoDb|null
     */
    protected $_model = null;

    protected function setUp()
    {
        if (defined('MONGODB_CONNECTION_STRING')) {
            $this->_connectionString = MONGODB_CONNECTION_STRING;
        }
        if (empty($this->_connectionString) || !extension_loaded('mongo')) {
            $this->markTestSkipped(
                "Either 'mongo' extension is not loaded or 'MONGODB_CONNECTION_STRING' constant is not defined"
            );
        }
        if (defined('MONGODB_DATABASE_NAME')) {
            $this->_dbName = MONGODB_DATABASE_NAME;
        }
        $this->_model = new \Magento\Framework\Cache\Backend\MongoDb(
            array('connection_string' => $this->_connectionString, 'db' => $this->_dbName)
        );
    }

    protected function tearDown()
    {
        if (!empty($this->_connectionString) && extension_loaded('mongo')) {
            $this->_model = null;
            $connection = new \Mongo($this->_connectionString);
            $connection->dropDB($this->_dbName);
        }
    }

    /**
     * @expectedException \Zend_Cache_Exception
     * @expectedExceptionMessage 'db' option is not specified
     */
    public function testConstructorException()
    {
        new \Magento\Framework\Cache\Backend\MongoDb();
    }

    public function testGetIds()
    {
        $this->assertEmpty($this->_model->getIds());
        $this->_model->save('test data 1', 'test1');
        $this->_model->save('test data 2', 'test2');
        $this->assertEquals(array('test1', 'test2'), $this->_model->getIds());
    }

    public function testGetTags()
    {
        $this->assertEmpty($this->_model->getTags());
        $this->_model->save('test data 1', 'test1', array('tag1', 'tag2'));
        $this->_model->save('test data 2', 'test2', array('tag1', 'tag3'));
        $actual = $this->_model->getTags();
        $expected = array('tag1', 'tag2', 'tag3');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider getIdsMatchingTagsDataProvider
     */
    public function testGetIdsMatchingTags($searchTags, $expectedIds)
    {
        $this->_prepareCollection();
        $actualIds = $this->_model->getIdsMatchingTags($searchTags);
        $this->assertEquals($expectedIds, $actualIds);
    }

    public function getIdsMatchingTagsDataProvider()
    {
        return array(
            'one tag' => array(array('tag1'), array('test1', 'test2', 'test3')),
            'multiple tags' => array(array('tag1', 'tag2'), array('test1', 'test3'))
        );
    }

    /**
     * @dataProvider getIdsNotMatchingTagsDataProvider
     */
    public function testGetIdsNotMatchingTags($searchTags, $expectedIds)
    {
        $this->_prepareCollection();
        $actualIds = $this->_model->getIdsNotMatchingTags($searchTags);
        $this->assertEquals($expectedIds, $actualIds);
    }

    public function getIdsNotMatchingTagsDataProvider()
    {
        return array(
            'one tag' => array(array('tag2'), array('test2', 'test4', 'test5')),
            'multiple tags' => array(array('tag1', 'tag2'), array('test4', 'test5'))
        );
    }

    /**
     * @dataProvider getIdsMatchingAnyTagsDataProvider
     */
    public function testGetIdsMatchingAnyTags($searchTags, $expectedIds)
    {
        $this->_prepareCollection();
        $actualIds = $this->_model->getIdsMatchingAnyTags($searchTags);
        $this->assertEquals($expectedIds, $actualIds);
    }

    public function getIdsMatchingAnyTagsDataProvider()
    {
        return array(
            'no tags' => array(array(), array()),
            'one tag' => array(array('tag2'), array('test1', 'test3')),
            'multiple tags' => array(array('tag1', 'tag2'), array('test1', 'test2', 'test3'))
        );
    }

    public function testGetMetadatas()
    {
        $cacheId = 'test';
        $tags = array('tag_1', 'tag_2');
        $this->_model->save('test data', $cacheId, $tags, 100);
        $actualResult = $this->_model->getMetadatas($cacheId);
        $this->assertArrayHasKey('expire', $actualResult);
        $this->assertArrayHasKey('tags', $actualResult);
        $this->assertArrayHasKey('mtime', $actualResult);
        $this->assertSame($tags, $actualResult['tags']);
    }

    /**
     * @param int $extraLifeTime
     * @param \PHPUnit_Framework_Constraint $constraint
     * @dataProvider touchDataProvider
     */
    public function testTouch($extraLifeTime, \PHPUnit_Framework_Constraint $constraint)
    {
        $cacheId = 'test';
        $this->_model->save('test data', $cacheId, array(), 2);
        $this->assertGreaterThan(0, $this->_model->test($cacheId), "Cache with id '{$cacheId}' has not been saved");
        $this->_model->touch($cacheId, $extraLifeTime);
        sleep(2);
        $this->assertThat($this->_model->test($cacheId), $constraint);
    }

    public function touchDataProvider()
    {
        return array(
            'not enough extra lifetime' => array(0, $this->isFalse()),
            'enough extra lifetime' => array(1000, $this->logicalNot($this->isFalse()))
        );
    }

    /**
     * @param string $data
     * @param int|bool|null $lifetime
     * @param bool $doNotTestValidity
     * @param string|bool $expected
     * @dataProvider loadDataProvider
     */
    public function testLoad($data, $lifetime, $doNotTestValidity, $expected)
    {
        $cacheId = 'test';
        $this->_model->save($data, $cacheId, array(), $lifetime);
        $actualData = $this->_model->load($cacheId, $doNotTestValidity);
        $this->assertSame($expected, $actualData);
    }

    public function loadDataProvider()
    {
        return array(
            'infinite lifetime with validity' => array('test data', null, false, 'test data'),
            'infinite lifetime without validity' => array('test data', null, true, 'test data'),
            'zero lifetime with validity' => array('test data', 0, false, false),
            'zero lifetime without validity' => array('test data', 0, true, 'test data')
        );
    }

    public function testTest()
    {
        $this->assertFalse($this->_model->test('test'));
        $this->_model->save('test data', 'test');
        $this->assertNotEmpty($this->_model->test('test'), "Cache with id 'test' has not been saved");
    }

    public function testSave()
    {
        $cacheId = 'test_id';
        $data = 'test data';
        $tags = array('tag1', 'tag2');

        $this->assertTrue($this->_model->save($data, $cacheId, $tags));
        $actualData = $this->_model->load($cacheId);
        $this->assertEquals($data, $actualData);
        $actualMetadata = $this->_model->getMetadatas($cacheId);
        $this->arrayHasKey('tags', $actualMetadata);
        $this->assertEquals($tags, $actualMetadata['tags']);
    }

    public function testRemove()
    {
        $cacheId = 'test';
        $this->_model->save('test data', $cacheId);
        $this->assertGreaterThan(0, $this->_model->test($cacheId), "Cache with id '{$cacheId}' has not been found");
        $this->_model->remove($cacheId);
        $this->assertFalse($this->_model->test($cacheId), "Cache with id '{$cacheId}' has not been removed");
    }

    /**
     * @dataProvider cleanDataProvider
     */
    public function testClean($mode, $tags, $expectedIds)
    {
        $this->_prepareCollection();

        $this->_model->clean($mode, $tags);
        $actualIds = $this->_model->getIds();
        $this->assertEquals($expectedIds, $actualIds);
    }

    public function cleanDataProvider()
    {
        return array(
            'clean all cache' => array(\Zend_Cache::CLEANING_MODE_ALL, array(), array()),
            'clean cache matching all tags' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('tag1', 'tag2'),
                array('test2', 'test4', 'test5')
            ),
            'clean cache not matching tags' => array(
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                array('tag1', 'tag2'),
                array('test1', 'test2', 'test3')
            ),
            'clean cache matching any tags' => array(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                array('tag1', 'tag2'),
                array('test4', 'test5')
            )
        );
    }

    public function testCleanOld()
    {
        $this->_model->save('long-living entity', 'long', array(), 1000);
        $this->_model->save('infinite-living entity', 'infinite', array(), null);
        $this->_model->save('short-living entity', 'short', array(), 0);
        $this->_model->clean(\Zend_Cache::CLEANING_MODE_OLD);
        $expectedIds = array('long', 'infinite');
        $actualIds = $this->_model->getIds();
        $this->assertSame($expectedIds, $actualIds);
    }

    /**
     * Fill the collection with data
     */
    protected function _prepareCollection()
    {
        $this->_model->save('test data 1', 'test1', array('tag1', 'tag2', 'tag3'));
        $this->_model->save('test data 2', 'test2', array('tag1', 'tag3'));
        $this->_model->save('test data 3', 'test3', array('tag2', 'tag1'));
        $this->_model->save('test data 4', 'test4', array('tag4', 'tag5'));
        $this->_model->save('test data 5', 'test5', array());
    }
}

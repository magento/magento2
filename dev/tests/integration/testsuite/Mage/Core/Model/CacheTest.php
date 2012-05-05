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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_CacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Cache();

        /* Setup preconditions: $this->_model->canUse('config') is true */
        $this->_model->save(serialize(array('config' => true)), Mage_Core_Model_Cache::OPTIONS_CACHE_ID);
    }

    public function tearDown()
    {
        /* Cleanup all cached data */
        $this->_model->flush();
    }

    /**
     * @dataProvider constructorDataProvider
     */
    public function testConstructor(array $options, $expectedBackendClass)
    {
        $model = new Mage_Core_Model_Cache($options);

        $backend = $model->getFrontend()->getBackend();
        $this->assertInstanceOf($expectedBackendClass, $backend);
    }

    /**
     * @return array
     */
    public function constructorDataProvider()
    {
        return array(
            array(array(), 'Zend_Cache_Backend_File'),
            array(array('backend' => 'File'), 'Zend_Cache_Backend_File'),
            array(array('backend' => 'File', 'backend_options' => array()), 'Zend_Cache_Backend_File'),
            array(array('backend' => 'Database'), 'Varien_Cache_Backend_Database'),
        );
    }

    /**
     * @param string $optionCode
     * @param string $extensionRequired
     * @dataProvider backendTwoLevelsDataProvider
     */
    public function testBackendTwoLevels($optionCode, $extensionRequired)
    {
        if ($extensionRequired) {
            if (!extension_loaded($extensionRequired)) {
                $this->markTestSkipped("The PHP extension '{$extensionRequired}' is required for this test.");

            }
        }
        $model = new Mage_Core_Model_Cache(array('backend' => $optionCode));
        $backend = $model->getFrontend()->getBackend();
        $this->assertInstanceOf('Zend_Cache_Backend_TwoLevels', $backend);
    }

    /**
     * @return array
     */
    public function backendTwoLevelsDataProvider()
    {
        return array(
            array('Memcached', 'memcached'),
            array('Memcached', 'memcache'),
        );
    }

    public function testGetFrontend()
    {
        $frontend = $this->_model->getFrontend();
        $this->assertInstanceOf('Varien_Cache_Core', $frontend);
    }

    public function testLoadSaveRemove()
    {
        $this->assertFalse($this->_model->load('non_existing_cache_id'));

        $cacheId = 'cache_id_' . __METHOD__;
        $expectedData = "Some data for $cacheId";

        $this->assertTrue($this->_model->save($expectedData, $cacheId));
        $this->assertEquals($expectedData, $this->_model->load($cacheId));

        $this->_model->remove($cacheId);
        $this->assertFalse($this->_model->load($cacheId));
    }

    /**
     * @dataProvider cleanDataProvider
     */
    public function testClean(array $cacheData, array $cleanCacheTags, array $expectedCacheIds)
    {
        /* Fill cache with predefined tagged data */
        foreach ($cacheData as $cacheId => $cacheTags) {
            $this->_model->save("data_for_$cacheId", $cacheId, $cacheTags);
        }

        /* Clean cache by tags */
        $this->_model->clean($cleanCacheTags);

        /* Check removed data */
        foreach (array_keys($cacheData) as $cacheId) {
            $cacheData = $this->_model->load($cacheId);
            if (in_array($cacheId, $expectedCacheIds)) {
                $this->assertNotEmpty($cacheData);
            } else {
                $this->assertFalse($cacheData);
            }
        }
    }

    public function cleanDataProvider()
    {
        $cacheData = array(
            'cache_id_1' => array('unique_tag_1'),
            'cache_id_2' => array('shared_tag'),
            'cache_id_3' => array(),
            'cache_id_4' => array('unique_tag_4', 'shared_tag'),
            'cache_id_5' => array('unique_tag_5'),
        );
        return array(
            'no tags' => array(
                $cacheData,
                array(),
                array()
            ),
            'app tag' => array(
                $cacheData,
                array(Mage_Core_Model_App::CACHE_TAG),
                array()
            ),
            'unique tag' => array(
                $cacheData,
                array('unique_tag_1'),
                array('cache_id_2', 'cache_id_3', 'cache_id_4', 'cache_id_5')
            ),
            'few unique tags' => array(
                $cacheData,
                array('unique_tag_1', 'unique_tag_5'),
                array('cache_id_2', 'cache_id_3', 'cache_id_4')
            ),
            'shared tag' => array(
                $cacheData,
                array('shared_tag'),
                array('cache_id_1', 'cache_id_3', 'cache_id_5')
            )
        );
    }

    public function testFlush()
    {
        $this->_model->save('data_for_cache_id_1', 'cache_id_1', array('tag_1'));
        $this->_model->save('data_for_cache_id_2', 'cache_id_2', array('tag_2'));
        $this->_model->save('data_for_cache_id_3', 'cache_id_3');
        $this->_model->flush();
        $this->assertFalse($this->_model->load('cache_id_1'));
        $this->assertFalse($this->_model->load('cache_id_2'));
        $this->assertFalse($this->_model->load('cache_id_3'));
    }

    public function testGetDbAdapter()
    {
        $this->assertInstanceOf('Zend_Db_Adapter_Abstract', $this->_model->getDbAdapter());
    }

    /**
     * @return Mage_Core_Model_Cache
     */
    public function testCanUse()
    {
        $this->assertEquals(array('config' => true), $this->_model->canUse(''));
        $this->assertTrue($this->_model->canUse('config'));
        return $this->_model;
    }

    /**
     * @depends testCanUse
     * @param Mage_Core_Model_Cache $model
     * @return Mage_Core_Model_CacheTest
     */
    public function testBanUse(Mage_Core_Model_Cache $model)
    {
        $this->assertTrue($model->canUse('config'));
        $model->banUse('config');
        $this->assertFalse($model->canUse('config'));
        return $model;
    }

    /**
     * @depends testBanUse
     * @param Mage_Core_Model_Cache $model
     */
    public function testAllowUse(Mage_Core_Model_Cache $model)
    {
        $this->assertFalse($model->canUse('config'));
        $model->allowUse('config');
        $this->assertTrue($model->canUse('config'));
    }

    /**
     * @dataProvider getTagsByTypeDataProvider
     */
    public function testGetTagsByType($cacheType, $expectedTags)
    {
        $actualTags = $this->_model->getTagsByType($cacheType);
        $this->assertEquals($expectedTags, $actualTags);
    }

    public function getTagsByTypeDataProvider()
    {
        return array(
            array('config',       array('CONFIG')),
            array('layout',       array('LAYOUT_GENERAL_CACHE_TAG')),
            array('block_html',   array('BLOCK_HTML')),
            array('translate',    array('TRANSLATE')),
            array('collections',  array('COLLECTION_DATA')),
            array('non-existing', false),
        );
    }

    public function testGetTypes()
    {
        /* Expect cache types introduced by Mage_Core module which can not be disabled */
        $expectedCacheTypes = array('config', 'layout', 'block_html', 'translate', 'collections');
        $expectedKeys = array('id', 'cache_type', 'description', 'tags', 'status');

        $actualCacheTypesData = $this->_model->getTypes();
        $actualCacheTypes = array_keys($actualCacheTypesData);

        /* Assert that all expected cache types are present */
        $this->assertEquals($expectedCacheTypes, array_intersect($expectedCacheTypes, $actualCacheTypes));

        foreach ($actualCacheTypesData as $cacheTypeData) {
            /** @var $cacheTypeData Varien_Object */
            $this->assertInstanceOf('Varien_Object', $cacheTypeData);
            $this->assertEquals($expectedKeys, array_keys($cacheTypeData->getData()));
        }
    }

    /**
     * @covers Mage_Core_Model_Cache::getInvalidatedTypes
     * @covers Mage_Core_Model_Cache::invalidateType
     */
    public function testInvalidatedTypes()
    {
        $this->assertEquals(array(), $this->_model->getInvalidatedTypes());

        $this->_model->invalidateType('config');

        $actualCacheTypes = $this->_model->getInvalidatedTypes();

        $this->assertEquals(array('config'), array_keys($actualCacheTypes));
        $this->assertInstanceOf('Varien_Object', $actualCacheTypes['config']);
    }

    public function testCleanType()
    {
        /* Setup preconditions */
        $this->_model->save('some data with layout cache tag', 'some_cache_id', array('LAYOUT_GENERAL_CACHE_TAG'));
        $this->_model->invalidateType('layout');

        $this->_model->cleanType('layout');

        $this->assertFalse($this->_model->load('some_cache_id'));
        $this->assertEquals(array(), $this->_model->getInvalidatedTypes());
    }

    public function testProcessRequestFalse()
    {
        $model = new Mage_Core_Model_Cache(array());
        $this->assertFalse($model->processRequest());

        $model = new Mage_Core_Model_Cache(array(
            'request_processors' => array('Mage_Core_Model_CacheTestRequestProcessor'),
        ));
        Mage_Core_Model_CacheTestRequestProcessor::$isEnabled = false;
        $this->assertFalse($model->processRequest());
    }

    public function testProcessRequestTrue()
    {
        if (!Magento_Test_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Test requires to send headers.');
        }
        $model = new Mage_Core_Model_Cache(array(
            'request_processors' => array('Mage_Core_Model_CacheTestRequestProcessor'),
        ));
        Mage_Core_Model_CacheTestRequestProcessor::$isEnabled = true;
        $this->assertTrue($model->processRequest());
    }

}

class Mage_Core_Model_CacheTestRequestProcessor
{
    public static $isEnabled;

    public function extractContent($content)
    {
        if (self::$isEnabled && $content === false) {
            return 'some content from cache';
        }
        return $content;
    }
}


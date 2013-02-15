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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_CacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_Config_Primary
     */
    protected $_primaryConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirsMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject;
     */
    protected $_cacheFrontend;

    protected function setUp()
    {
        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $this->_helperMock = $this->getMock('Mage_Core_Helper_Data', array('__'), array(), '', false);
        $this->_helperMock
            ->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        $this->_helperFactoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_helperMock));

        $this->_dirsMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false);

        $this->_primaryConfigMock = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);

        $this->_configMock = new Mage_Core_Model_Config_Base(<<<XML
            <config>
                <global>
                    <cache>
                        <types>
                            <single_tag>
                                <label>Tag One</label>
                                <description>This is Tag One</description>
                                <tags>tag_one</tags>
                            </single_tag>
                            <multiple_tags>
                                <label>Tags One and Two</label>
                                <description>These are Tags One and Two</description>
                                <tags>tag_one,tag_two</tags>
                            </multiple_tags>
                        </types>
                    </cache>
                </global>
            </config>
XML
        );

        $this->_cacheFrontend = $this->getMock(
            'Zend_Cache_Core', array('load', 'test', 'save', 'remove', 'clean', '_getHelper')
        );
        $this->_model = new Mage_Core_Model_Cache(
            $this->_configMock, $this->_primaryConfigMock, $this->_dirsMock, $this->_helperFactoryMock, false, array(
                'frontend' => $this->_cacheFrontend,
                'backend'  => 'BlackHole',
            )
        );
    }

    protected function tearDown()
    {
        $this->_primaryConfigMock = null;
        $this->_configMock = null;
        $this->_dirsMock = null;
        $this->_helperFactoryMock = null;
        $this->_helperMock = null;
        $this->_cacheFrontend = null;
        $this->_model = null;
    }

    /**
     * Force to load desired cache type options
     *
     * @param array $cacheTypeOptions
     */
    protected function _emulateCacheTypeOptions(array $cacheTypeOptions = array('config' => true))
    {
        $this->_cacheFrontend
            ->expects($this->any())
            ->method('load')
            ->with(strtoupper(Mage_Core_Model_Cache::OPTIONS_CACHE_ID))
            ->will($this->returnValue(serialize($cacheTypeOptions)))
        ;
    }

    /**
     * @dataProvider constructorDataProvider
     * @param array $options
     * @param string $expectedBackendClass
     */
    public function testConstructor(array $options, $expectedBackendClass)
    {
        $options += array('helper' => $this->_helperMock);
        $model = new Mage_Core_Model_Cache(
            $this->_configMock, $this->_primaryConfigMock, $this->_dirsMock,
            $this->_helperFactoryMock, false, $options
        );

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
        );
    }

    public function testGetFrontend()
    {
        $frontend = $this->_model->getFrontend();
        $this->assertSame($this->_cacheFrontend, $frontend);
    }

    public function testLoad()
    {
        $this->_cacheFrontend
            ->expects($this->once())
            ->method('load')
            ->with('TEST_ID')
            ->will($this->returnValue('test_data'))
        ;
        $this->assertEquals('test_data', $this->_model->load('test_id'));
    }

    /**
     * @dataProvider saveDataProvider
     * @param string|mixed $inputData
     * @param string $inputId
     * @param array $inputTags
     * @param string $expectedData
     * @param string $expectedId
     * @param array $expectedTags
     */
    public function testSave($inputData, $inputId, $inputTags, $expectedData, $expectedId, $expectedTags)
    {
        $this->_cacheFrontend
            ->expects($this->once())
            ->method('save')
            ->with($this->identicalTo($expectedData), $expectedId, $expectedTags)
        ;
        $this->_model->save($inputData, $inputId, $inputTags);
    }

    public function saveDataProvider()
    {
        $configTag = Mage_Core_Model_Config::CACHE_TAG;
        $appTag = Mage_Core_Model_AppInterface::CACHE_TAG;
        return array(
            'default tags' => array(
                'test_data', 'test_id', array(), 'test_data', 'TEST_ID', array($appTag)
            ),
            'config tags' => array(
                'test_data', 'test_id', array($configTag), 'test_data', 'TEST_ID', array($configTag)
            ),
            'lowercase tags' => array(
                'test_data', 'test_id', array('test_tag'), 'test_data', 'TEST_ID', array('TEST_TAG', $appTag)
            ),
            'non-string data' => array(
                1234567890, 'test_id', array(), '1234567890', 'TEST_ID', array(Mage_Core_Model_AppInterface::CACHE_TAG)
            ),
        );
    }

    public function testSaveDisallowed()
    {
        $model = new Mage_Core_Model_Cache(
            $this->_configMock, $this->_primaryConfigMock, $this->_dirsMock, $this->_helperFactoryMock, array(
            'frontend' => $this->_cacheFrontend,
            'backend'  => 'BlackHole',
            'disallow_save' => true
        ));
        $this->_cacheFrontend
            ->expects($this->never())
            ->method('save')
        ;
        $model->save('test_data', 'test_id');
    }

    /**
     * @dataProvider successFailureDataProvider
     * @param bool $result
     */
    public function testRemove($result)
    {
        $this->_cacheFrontend
            ->expects($this->once())
            ->method('remove')
            ->with('TEST_ID')
            ->will($this->returnValue($result))
        ;
        $this->assertEquals($result, $this->_model->remove('test_ID'));
    }

    public function successFailureDataProvider()
    {
        return array(
            'success' => array(true),
            'failure' => array(false),
        );
    }

    /**
     * @dataProvider cleanDataProvider
     * @param array $inputTags
     * @param array $expectedTags
     */
    public function testClean(array $inputTags, array $expectedTags)
    {
        $this->_cacheFrontend
            ->expects($this->once())
            ->method('clean')
            ->with(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $expectedTags)
            ->will($this->returnValue(false))
        ;
        $this->_model->clean($inputTags);
    }

    public function cleanDataProvider()
    {
        return array(
            'default tags' => array(array(), array(Mage_Core_Model_AppInterface::CACHE_TAG)),
            'custom tags'  => array(array('test_tag'), array('TEST_TAG')),
        );
    }

    public function testCleanByConfig()
    {
        $this->_cacheFrontend
            ->expects($this->at(0))
            ->method('clean')
            ->with(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(Mage_Core_Model_AppInterface::CACHE_TAG))
            ->will($this->returnValue(true))
        ;
        $this->_cacheFrontend
            ->expects($this->at(1))
            ->method('clean')
            ->with(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(Mage_Core_Model_Config::CACHE_TAG))
            ->will($this->returnValue(true))
        ;
        $this->_model->clean();
    }

    /**
     * @dataProvider successFailureDataProvider
     * @param bool $result
     */
    public function testFlush($result)
    {
        $this->_cacheFrontend
            ->expects($this->once())
            ->method('clean')
            ->will($this->returnValue($result))
        ;
        $this->assertEquals($result, $this->_model->flush());
    }

    /**
     * @return Mage_Core_Model_Cache
     */
    public function testCanUse()
    {
        $this->_emulateCacheTypeOptions();
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
        $this->_emulateCacheTypeOptions();
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
        $this->_emulateCacheTypeOptions();
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
            'single tag'    => array('single_tag',    array('tag_one')),
            'multiple tags' => array('multiple_tags', array('tag_one', 'tag_two')),
            'non-existing'  => array('non-existing',  false),
        );
    }

    public function testGetTypes()
    {
        $expectedCacheTypes = array(
            'single_tag' => array(
                'id'          => 'single_tag',
                'cache_type'  => 'Tag One',
                'description' => 'This is Tag One',
                'tags'        => 'TAG_ONE',
                'status'      => 0,
            ),
            'multiple_tags' => array(
                'id'          => 'multiple_tags',
                'cache_type'  => 'Tags One and Two',
                'description' => 'These are Tags One and Two',
                'tags'        => 'TAG_ONE,TAG_TWO',
                'status'      => 0,
            ),
        );
        $actualCacheTypes = $this->_model->getTypes();
        $this->assertInternalType('array', $actualCacheTypes);
        $this->assertEquals(array_keys($expectedCacheTypes), array_keys($actualCacheTypes));
        foreach ($actualCacheTypes as $cacheId => $cacheTypeData) {
            /** @var $cacheTypeData Varien_Object */
            $this->assertInstanceOf('Varien_Object', $cacheTypeData);
            $this->assertEquals($expectedCacheTypes[$cacheId], $cacheTypeData->getData());
        }
    }

    public function testGetInvalidatedTypes()
    {
        $this->_model->allowUse('single_tag');
        $this->_cacheFrontend
            ->expects($this->once())
            ->method('load')
            ->with(strtoupper(Mage_Core_Model_Cache::INVALIDATED_TYPES))
            ->will($this->returnValue(serialize(array('single_tag' => 1, 'non_existing_type' => 1))))
        ;
        $actualResult = $this->_model->getInvalidatedTypes();
        $this->assertInternalType('array', $actualResult);
        $this->assertCount(1, $actualResult);
        $this->assertArrayHasKey('single_tag', $actualResult);
        $this->assertInstanceOf('Varien_Object', $actualResult['single_tag']);
    }

    public function testInvalidateType()
    {
        $this->_cacheFrontend
            ->expects($this->once())
            ->method('save')
            ->with(serialize(array('test' => 1)), strtoupper(Mage_Core_Model_Cache::INVALIDATED_TYPES))
        ;
        $this->_model->invalidateType('test');
    }

    public function testCleanType()
    {
        $this->_model->allowUse('single_tag');
        $this->_model->allowUse('multiple_tags');
        $this->_cacheFrontend
            ->expects($this->at(0))
            ->method('clean')
            ->with(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('TAG_ONE', 'TAG_TWO'))
        ;
        $this->_cacheFrontend
            ->expects($this->at(1))
            ->method('load')
            ->with(strtoupper(Mage_Core_Model_Cache::INVALIDATED_TYPES))
            ->will($this->returnValue(serialize(array('single_tag' => 1, 'multiple_tags' => 1))))
        ;
        $this->_cacheFrontend
            ->expects($this->at(2))
            ->method('save')
            ->with(serialize(array('single_tag' => 1)), strtoupper(Mage_Core_Model_Cache::INVALIDATED_TYPES))
        ;
        $this->_model->cleanType('multiple_tags');
    }
}

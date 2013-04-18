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
     * @var PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_cacheTypeMocks;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheFrontendMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheTypesMock;

    protected function setUp()
    {
        $helperMock = $this->getMock('Mage_Core_Helper_Data', array('__'), array(), '', false);
        $helperMock
            ->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0))
        ;
        $helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $helperFactoryMock
            ->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Helper_Data')
            ->will($this->returnValue($helperMock))
        ;

        $this->_initCacheTypeMocks();
        $objectManagerMock = $this->getMockForAbstractClass('Magento_ObjectManager');
        $objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(array($this, 'getTypeMock')));

        $this->_cacheFrontendMock = $this->getMockForAbstractClass(
            'Magento_Cache_FrontendInterface', array(), '', true, true, true, array('clean')
        );

        $frontendPoolMock = $this->getMock('Mage_Core_Model_Cache_Frontend_Pool', array(), array(), '', false);
        $frontendPoolMock
            ->expects($this->any())
            ->method('valid')
            ->will($this->onConsecutiveCalls(true, false))
        ;
        $frontendPoolMock
            ->expects($this->any())
            ->method('current')
            ->will($this->returnValue($this->_cacheFrontendMock))
        ;
        $frontendPoolMock
            ->expects($this->any())
            ->method('get')
            ->with(Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID)
            ->will($this->returnValue($this->_cacheFrontendMock))
        ;

        $this->_cacheTypesMock = $this->getMock('Mage_Core_Model_Cache_Types', array(), array(), '', false);

        $configFixture = new Mage_Core_Model_Config_Base(file_get_contents(__DIR__ . '/_files/cache_types.xml'));

        $dirsMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false);

        $this->_model = new Mage_Core_Model_Cache(
            $objectManagerMock, $frontendPoolMock, $this->_cacheTypesMock, $configFixture,
            $dirsMock, $helperFactoryMock
        );
    }

    /**
     * Init necessary cache type mocks
     */
    protected function _initCacheTypeMocks()
    {
        $cacheTypes = array('Magento_Cache_Frontend_Decorator_TagScope', 'Magento_Cache_Frontend_Decorator_Bare');
        foreach ($cacheTypes as $type) {
            $this->_cacheTypeMocks[$type] = $this->getMock($type, array('clean'), array(
                $this->getMockForAbstractClass('Magento_Cache_FrontendInterface'), 'FIXTURE_TAG'
            ));
        }
    }

    /**
     * Callback for the object manager to get different cache type mocks
     *
     * @param string $type Class of the cache type
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getTypeMock($type)
    {
        return $this->_cacheTypeMocks[$type];
    }

    protected function tearDown()
    {
        $this->_cacheTypeMocks = array();
        $this->_cacheTypesMock = null;
        $this->_cacheFrontendMock = null;
        $this->_model = null;
    }

    public function testConstructor()
    {
        $this->assertSame($this->_cacheFrontendMock, $this->_model->getFrontend());
    }

    public function testGetFrontend()
    {
        $frontend = $this->_model->getFrontend();
        $this->assertSame($this->_cacheFrontendMock, $frontend);
    }

    public function testLoad()
    {
        $this->_cacheFrontendMock
            ->expects($this->once())
            ->method('load')
            ->with('test_id')
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
        $this->_cacheFrontendMock
            ->expects($this->once())
            ->method('save')
            ->with($this->identicalTo($expectedData), $expectedId, $expectedTags)
        ;
        $this->_model->save($inputData, $inputId, $inputTags);
    }

    public function saveDataProvider()
    {
        $configTag = Mage_Core_Model_Config::CACHE_TAG;
        return array(
            'default tags' => array(
                'test_data', 'test_id', array(), 'test_data', 'test_id', array()
            ),
            'config tags' => array(
                'test_data', 'test_id', array($configTag), 'test_data', 'test_id', array($configTag)
            ),
            'lowercase tags' => array(
                'test_data', 'test_id', array('test_tag'), 'test_data', 'test_id', array('test_tag')
            ),
            'non-string data' => array(
                1234567890, 'test_id', array(), '1234567890', 'test_id', array()
            ),
        );
    }

    /**
     * @dataProvider successFailureDataProvider
     * @param bool $result
     */
    public function testRemove($result)
    {
        $this->_cacheFrontendMock
            ->expects($this->once())
            ->method('remove')
            ->with('test_id')
            ->will($this->returnValue($result))
        ;
        $this->assertEquals($result, $this->_model->remove('test_id'));
    }

    public function successFailureDataProvider()
    {
        return array(
            'success' => array(true),
            'failure' => array(false),
        );
    }

    public function testCleanByTags()
    {
        $expectedTags = array('test_tag');
        $this->_cacheFrontendMock
            ->expects($this->once())
            ->method('clean')
            ->with(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $expectedTags)
            ->will($this->returnValue(true))
        ;
        $this->assertTrue($this->_model->clean($expectedTags));
    }

    public function testCleanByEmptyTags()
    {
        $this->_cacheFrontendMock
            ->expects($this->once())
            ->method('clean')
            ->with(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array(Mage_Core_Model_AppInterface::CACHE_TAG))
            ->will($this->returnValue(true))
        ;
        $this->assertTrue($this->_model->clean());
    }

    public function testCanUse()
    {
        $this->_cacheTypesMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('config')
            ->will($this->returnValue(true))
        ;
        $this->assertTrue($this->_model->canUse('config'));
    }

    public function testBanUse()
    {
        $this->_cacheTypesMock
            ->expects($this->once())
            ->method('setEnabled')
            ->with('config', false)
        ;
        $this->_model->banUse('config');
    }

    public function testAllowUse()
    {
        $this->_cacheTypesMock
            ->expects($this->once())
            ->method('setEnabled')
            ->with('config', true)
        ;
        $this->_model->allowUse('config');
    }

    public function testGetTypes()
    {
        $expectedCacheTypes = array(
            'fixture_type_tag_scope' => array(
                'id'          => 'fixture_type_tag_scope',
                'cache_type'  => 'Fixture Type One',
                'description' => 'This is Fixture Type One',
                'tags'        => 'FIXTURE_TAG',
                'status'      => 0,
            ),
            'fixture_type_bare' => array(
                'id'          => 'fixture_type_bare',
                'cache_type'  => 'Fixture Type Two',
                'description' => 'This is Fixture Type Two',
                'tags'        => '',
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
        $this->_cacheTypesMock
            ->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true))
        ;
        $this->_cacheFrontendMock
            ->expects($this->once())
            ->method('load')
            ->with(Mage_Core_Model_Cache::INVALIDATED_TYPES)
            ->will($this->returnValue(serialize(array('fixture_type_tag_scope' => 1))))
        ;
        $actualResult = $this->_model->getInvalidatedTypes();
        $this->assertInternalType('array', $actualResult);
        $this->assertCount(1, $actualResult);
        $this->assertArrayHasKey('fixture_type_tag_scope', $actualResult);
        $this->assertInstanceOf('Varien_Object', $actualResult['fixture_type_tag_scope']);
    }

    public function testInvalidateType()
    {
        $this->_cacheFrontendMock
            ->expects($this->once())
            ->method('save')
            ->with(serialize(array('test' => 1)), Mage_Core_Model_Cache::INVALIDATED_TYPES)
        ;
        $this->_model->invalidateType('test');
    }

    public function testCleanType()
    {
        $this->_cacheTypeMocks['Magento_Cache_Frontend_Decorator_TagScope']
            ->expects($this->once())
            ->method('clean')
        ;
        $this->_cacheTypeMocks['Magento_Cache_Frontend_Decorator_Bare']
            ->expects($this->never())
            ->method('clean')
        ;
        $this->_model->cleanType('fixture_type_tag_scope');
    }
}

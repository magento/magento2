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
 * @package     Magento_ObjectManager
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_ObjectManager_Zend
 */
class Magento_ObjectManager_ZendTest extends PHPUnit_Framework_TestCase
{
    /**
     * Area code
     */
    const AREA_CODE = 'global';

    /**
     * Class name
     */
    const CLASS_NAME = 'TestClassName';

    /**#@+
     * Objects for create and get method
     */
    const OBJECT_CREATE = 'TestObjectCreate';
    const OBJECT_GET = 'TestObjectGet';
    /**#@-*/

    /**
     * Arguments
     *
     * @var array
     */
    protected $_arguments = array(
        'argument_1' => 'value_1',
        'argument_2' => 'value_2',
    );

    /**
     * Expected instance manager parametrized cache after clear
     *
     * @var array
     */
    protected $_instanceCache = array(
        'hashShort' => array(),
        'hashLong'  => array()
    );

    /**
     * ObjectManager instance for tests
     *
     * @var Magento_ObjectManager_Zend
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Config|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_magentoConfig;

    /**
     * @var Zend\Di\InstanceManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_instanceManager;

    /**
     * @var Zend\Di\Di|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_diInstance;

    protected function tearDown()
    {
        unset($this->_objectManager);
        unset($this->_magentoConfig);
        unset($this->_instanceManager);
        unset($this->_diInstance);
    }

    /**
     * @dataProvider constructDataProvider
     * @param string $definitionsFile
     * @param Zend\Di\Di $diInstance
     */
    public function testConstructWithDiObject($definitionsFile, $diInstance)
    {
        $model = new Magento_ObjectManager_Zend($definitionsFile, $diInstance);
        $this->assertAttributeInstanceOf(get_class($diInstance), '_di', $model);
    }

    /**
     * @dataProvider loadAreaConfigurationDataProvider
     * @param string $expectedAreaCode
     * @param string $actualAreaCode
     */
    public function testLoadAreaConfiguration($expectedAreaCode, $actualAreaCode)
    {
        $this->_prepareObjectManagerForLoadAreaConfigurationTests($expectedAreaCode);
        if ($actualAreaCode) {
            $this->_objectManager->loadAreaConfiguration($actualAreaCode);
        } else {
            $this->_objectManager->loadAreaConfiguration();
        }
    }

    public function testCreate()
    {
        $this->_prepareObjectManagerForGetCreateTests(true);
        $actualObject = $this->_objectManager->create(self::CLASS_NAME, $this->_arguments);
        $this->assertEquals(self::OBJECT_CREATE, $actualObject);
    }

    public function testGet()
    {
        $this->_prepareObjectManagerForGetCreateTests(false);
        $actualObject = $this->_objectManager->get(self::CLASS_NAME, $this->_arguments);
        $this->assertEquals(self::OBJECT_GET, $actualObject);
    }

    /**
     * Create Magento_ObjectManager_Zend instance for testLoadAreaConfiguration
     *
     * @param string $expectedAreaCode
     */
    protected function _prepareObjectManagerForLoadAreaConfigurationTests($expectedAreaCode)
    {
        /** @var $modelConfigMock Mage_Core_Model_Config */
        $this->_magentoConfig = $this->getMock('Mage_Core_Model_Config', array('getNode', 'loadBase'),
            array(), '', false
        );

        $nodeMock = $this->getMock('Varien_Object', array('asArray'), array(), '', false);
        $nodeArrayValue = array('alias' => array(1));
        $nodeMock->expects($this->once())
            ->method('asArray')
            ->will($this->returnValue($nodeArrayValue));

        $expectedConfigPath = $expectedAreaCode . '/' . Magento_ObjectManager_Zend::CONFIGURATION_DI_NODE;
        $this->_magentoConfig->expects($this->once())
            ->method('getNode')
            ->with($expectedConfigPath)
            ->will($this->returnValue($nodeMock));

        /** @var $instanceManagerMock Zend\Di\InstanceManager */
        $this->_instanceManager = $this->getMock('Zend\Di\InstanceManager',
            array('addSharedInstance', 'addAlias'), array(), '', false);
        $this->_instanceManager->expects($this->once())
            ->method('addAlias');

        /** @var $diMock Zend\Di\Di */
        $this->_diInstance = $this->getMock('Zend\Di\Di',
            array('instanceManager', 'get'), array(), '', false);
        $this->_diInstance->expects($this->exactly(2))
            ->method('instanceManager')
            ->will($this->returnValue($this->_instanceManager));
        $this->_diInstance->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->_magentoConfig));

        $this->_objectManager = new Magento_ObjectManager_Zend(null, $this->_diInstance);
    }

    /**
     * Create Magento_ObjectManager_Zend instance
     *
     * @param bool $mockNewInstance
     */
    protected function _prepareObjectManagerForGetCreateTests($mockNewInstance = false)
    {
        $this->_magentoConfig = $this->getMock('Mage_Core_Model_Config',
            array('loadBase'), array(), '', false);
        $this->_magentoConfig->expects($this->any())
            ->method('loadBase')
            ->will($this->returnSelf());

        $this->_instanceManager = $this->getMock('Zend\Di\InstanceManager', array('addSharedInstance'),
            array(), '', false
        );
        $this->_diInstance = $this->getMock('Zend\Di\Di',
            array('instanceManager', 'newInstance', 'get', 'setDefinitionList')
        );
        $this->_diInstance->expects($this->any())
            ->method('instanceManager')
            ->will($this->returnValue($this->_instanceManager));
        if ($mockNewInstance) {
            $this->_diInstance->expects($this->once())
                ->method('newInstance')
                ->will($this->returnCallback(array($this, 'verifyCreate')));
        } else {
            $this->_diInstance->expects($this->once())
                ->method('get')
                ->will($this->returnCallback(array($this, 'verifyGet')));
        }

        $this->_objectManager = new Magento_ObjectManager_Zend(null, $this->_diInstance);
    }

    /**
     * Data Provider for method __construct($definitionsFile, $diInstance)
     *
     * @return array
     */
    public function constructDataProvider()
    {
        $this->_diInstance = $this->getMock('Zend\Di\Di',
            array('get', 'setDefinitionList', 'instanceManager')
        );
        $this->_magentoConfig = $this->getMock('Mage_Core_Model_Config', array('loadBase'),
            array(), '', false
        );
        $this->_instanceManager = $this->getMock('Zend\Di\InstanceManager', array('addSharedInstance'),
            array(), '', false
        );
        $this->_diInstance->expects($this->exactly(3))
            ->method('instanceManager')
            ->will($this->returnValue($this->_instanceManager));
        $this->_diInstance->expects($this->exactly(6))
            ->method('get')
            ->with('Mage_Core_Model_Config')
            ->will($this->returnCallback(array($this, 'getCallback')));
        $this->_diInstance->expects($this->exactly(4))
            ->method('setDefinitionList')
            ->will($this->returnCallback(array($this, 'verifySetDefinitionListCallback')));
        $this->_instanceManager->expects($this->exactly(3))
            ->method('addSharedInstance')
            ->will($this->returnCallback(array($this, 'verifyAddSharedInstanceCallback')));

        return array(
            'without definition file and with specific Di instance' => array(
                null, $this->_diInstance
            ),
            'with definition file and with specific Di instance' => array(
                __DIR__ . '/_files/test_definition_file', $this->_diInstance
            ),
            'with missing definition file and with specific Di instance' => array(
                'test_definition_file', $this->_diInstance
            )
        );
    }

    /**
     * Data provider for testLoadAreaConfiguration
     *
     * @return array
     */
    public function loadAreaConfigurationDataProvider()
    {
        return array(
            'specified area' => array(
                '$expectedAreaCode' => self::AREA_CODE,
                '$actualAreaCode'   => self::AREA_CODE,
            ),
            'default area' => array(
                '$expectedAreaCode' => Magento_ObjectManager_Zend::CONFIGURATION_AREA,
                '$actualAreaCode'   => null,
            ),
        );
    }

    /**
     * Callback to use instead Di::setDefinitionList
     *
     * @param Zend\Di\DefinitionList $definitions
     */
    public function verifySetDefinitionListCallback(Zend\Di\DefinitionList $definitions)
    {
        $this->assertInstanceOf('Zend\Di\DefinitionList', $definitions);
    }

    /**
     * Callback to use instead InstanceManager::addSharedInstance
     *
     * @param object $instance
     * @param string $classOrAlias
     */
    public function verifyAddSharedInstanceCallback($instance, $classOrAlias)
    {
        $this->assertInstanceOf('Magento_ObjectManager_Zend', $instance);
        $this->assertEquals('Magento_ObjectManager', $classOrAlias);
    }

    /**
     * Callback to use instead Di::get
     *
     * @param string $className
     * @param array $arguments
     * @return Mage_Core_Model_Config
     */
    public function getCallback($className, array $arguments = array())
    {
        $this->assertEquals('Mage_Core_Model_Config', $className);
        $this->assertEmpty($arguments);
        return $this->_magentoConfig;
    }

    /**
     * Callback method for Zend\Di\Di::newInstance
     *
     * @param string $className
     * @param array $arguments
     * @return string
     */
    public function verifyCreate($className, array $arguments = array())
    {
        $this->assertEquals(self::CLASS_NAME, $className);
        $this->assertEquals($this->_arguments, $arguments);

        return self::OBJECT_CREATE;
    }

    /**
     * Callback method for Zend\Di\Di::get
     *
     * @param string $className
     * @param array $arguments
     * @return string|Mage_Core_Model_Config
     */
    public function verifyGet($className, array $arguments = array())
    {
        if ($className == 'Mage_Core_Model_Config') {
            return $this->_magentoConfig;
        }

        $this->assertEquals(self::CLASS_NAME, $className);
        $this->assertEquals($this->_arguments, $arguments);

        return self::OBJECT_GET;
    }
}

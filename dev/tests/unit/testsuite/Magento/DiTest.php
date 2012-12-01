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
 * @package     Magento_Di
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test case for Magento_Di
 */
class Magento_DiTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Parent classes for test classes
     */
    const PARENT_CLASS_MODEL = 'Mage_Core_Model_Abstract';
    const PARENT_CLASS_BLOCK = 'Mage_Core_Block_Abstract';
    /**#@-*/

    /**#@+
     * Test classes for different instantiation types
     */
    const TEST_CLASS_MODEL = 'Mage_Test_Model_TestStub';
    const TEST_CLASS_BLOCK = 'Mage_Test_Block_TestStub';
    const TEST_CLASS_OTHER = 'Varien_Object';
    /**#@-*/

    /**
     * Construct method name
     */
    const CONSTRUCT_METHOD = '__construct';

    /**
     * Model under test
     *
     * @var Magento_Di
     */
    protected $_model;

    /**
     * Expected property types
     *
     * @var array
     */
    protected $_propertyTypes = array(
        self::TEST_CLASS_MODEL => array(
            '_eventDispatcher' => 'Mage_Core_Model_Event_Manager',
            '_cacheManager'    => 'Mage_Core_Model_Cache',
        ),
        self::TEST_CLASS_BLOCK => array(
            '_request'         => 'Mage_Core_Controller_Request_Http',
            '_layout'          => 'Mage_Core_Model_Layout',
            '_eventManager'    => 'Mage_Core_Model_Event_Manager',
            '_urlBuilder'      => 'Mage_Core_Model_Url',
            '_translator'      => 'Mage_Core_Model_Translate',
            '_cache'           => 'Mage_Core_Model_Cache',
            '_designPackage'   => 'Mage_Core_Model_Design_Package',
            '_session'         => 'Mage_Core_Model_Session',
            '_storeConfig'     => 'Mage_Core_Model_Store_Config',
            '_frontController' => 'Mage_Core_Controller_Varien_Front',
            '_helperFactory'   => 'Mage_Core_Model_Factory_Helper',
        ),
    );

    /**
     * List of expected cached classes
     *
     * @var array
     */
    protected $_cachedInstances = array(
        self::TEST_CLASS_MODEL => array(
            'eventManager' => 'Mage_Core_Model_Event_Manager',
            'cache'        => 'Mage_Core_Model_Cache',
        ),
        self::TEST_CLASS_BLOCK => array(
            'eventManager'    => 'Mage_Core_Model_Event_Manager',
            'urlBuilder'      => 'Mage_Core_Model_Url',
            'cache'           => 'Mage_Core_Model_Cache',
            'request'         => 'Mage_Core_Controller_Request_Http',
            'layout'          => 'Mage_Core_Model_Layout',
            'translate'       => 'Mage_Core_Model_Translate',
            'design'          => 'Mage_Core_Model_Design_Package',
            'session'         => 'Mage_Core_Model_Session',
            'storeConfig'     => 'Mage_Core_Model_Store_Config',
            'frontController' => 'Mage_Core_Controller_Varien_Front',
            'helperFactory'   => 'Mage_Core_Model_Factory_Helper',
        ),
    );

    /**
     * Construct parameters full definition
     *
     * @var array
     */
    protected $_constructParameters = array(
        self::TEST_CLASS_MODEL => array(
            '::__construct:0' => array('eventDispatcher', 'Mage_Core_Model_Event_Manager', true, null),
            '::__construct:1' => array('cacheManager', 'Mage_Core_Model_Cache', true, null),
            '::__construct:2' => array('resource', 'Mage_Core_Model_Resource_Abstract', false, null),
            '::__construct:3' => array('resourceCollection', 'Varien_Data_Collection_Db', false, null),
            '::__construct:4' => array('data', null, false, array()),
        ),
    );

    /**
     * Test data value to assert test expectations
     *
     * @var array
     */
    protected $_expectedDataValue = array('key' => 'value');

    /**
     * List of shared instances
     *
     * @var array
     */
    protected $_sharedInstances = array();

    /**
     * Flag if class mocks exist
     *
     * @var bool
     */
    protected static $_isClassMocks = false;

    protected function setUp()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        if (!self::$_isClassMocks) {
            $this->getMockForAbstractClass(
                self::PARENT_CLASS_MODEL,
                $objectManagerHelper->getConstructArguments(Magento_Test_Helper_ObjectManager::MODEL_ENTITY,
                    self::PARENT_CLASS_MODEL
                ),
                self::TEST_CLASS_MODEL, false
            );
            $this->getMockForAbstractClass(self::PARENT_CLASS_BLOCK,
                $objectManagerHelper->getConstructArguments(Magento_Test_Helper_ObjectManager::BLOCK_ENTITY,
                    self::PARENT_CLASS_BLOCK
                ),
                self::TEST_CLASS_BLOCK, false
            );
            self::$_isClassMocks = true;
        }
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Data provider for testNewInstanceWithoutDefinitions
     *
     * @return array
     */
    public function newInstanceWithoutDefinitionsDataProvider()
    {
        return array(
            'shared model instance with arguments' => array(
                '$className' => self::TEST_CLASS_MODEL,
                '$arguments' => array(1 => 'value_1'),
                '$isShared'  => true,
            ),
            'shared model instance without arguments' => array(
                '$className' => self::TEST_CLASS_MODEL,
                '$arguments' => array(),
                '$isShared'  => true,
            ),
            'not shared model instance' => array(
                '$className' => self::TEST_CLASS_MODEL,
                '$arguments' => array(),
                '$isShared'  => false,
            ),
            'not shared block instance' => array(
                '$className' => self::TEST_CLASS_BLOCK,
                '$arguments' => array(),
                '$isShared'  => false,
            ),
            'not shared other class instance' => array(
                '$className' => self::TEST_CLASS_OTHER,
                '$arguments' => array(),
                '$isShared'  => false,
            ),
        );
    }

    /**
     * @param string $className
     * @param array $arguments
     * @param bool $isShared
     *
     * @dataProvider newInstanceWithoutDefinitionsDataProvider
     */
    public function testNewInstanceWithoutDefinitions($className, array $arguments = array(), $isShared = true)
    {
        // assert object instantiation
        $this->_prepareMockForNewInstanceWithoutDefinitions($className, $arguments, $isShared);
        $testObject = $this->_model->newInstance($className, $arguments, $isShared);
        switch ($className) {
            case self::TEST_CLASS_MODEL:
                $this->_assertTestModel($testObject, $arguments);
                break;

            case self::TEST_CLASS_BLOCK:
                $this->_assertTestBlock($testObject, $arguments);
                break;

            case self::TEST_CLASS_OTHER:
            default:
                $this->assertInstanceOf($className, $testObject);
                break;
        }
        $this->assertAttributeEmpty('instanceContext', $this->_model);

        // assert cache
        if (isset($this->_cachedInstances[$className])) {
            $expectedCache = array();
            foreach ($this->_cachedInstances[$className] as $class) {
                $this->assertArrayHasKey($class, $this->_sharedInstances);
                $expectedCache[$class] = $this->_sharedInstances[$class];
            }
            $this->assertAttributeEquals($expectedCache, '_cachedInstances', $this->_model);
        }
    }

    /**
     * Prepares all mocks for testNewInstanceWithoutDefinitions
     *
     * @param string $className
     * @param bool $isShared
     * @param array $arguments
     */
    protected function _prepareMockForNewInstanceWithoutDefinitions(
        $className, array $arguments = array(), $isShared = true
    ) {
        $definitions = $this->getMock('Zend\Di\DefinitionList', array('hasClass'), array(), '', false);
        $definitions->expects($this->once())
            ->method('hasClass')
            ->will($this->returnValue(false));

        $instanceManager = $this->getMock(
            'Zend\Di\InstanceManager',
            array('hasSharedInstance', 'getSharedInstance', 'addSharedInstanceWithParameters', 'addSharedInstance')
        );
        $instanceManager->expects($this->any())
            ->method('hasSharedInstance')
            ->will($this->returnValue(true));
        $instanceManager->expects($this->any())
            ->method('getSharedInstance')
            ->will($this->returnCallback(array($this, 'callbackGetSharedInstance')));

        if ($isShared) {
            if ($arguments) {
                $instanceManager->expects($this->once())
                    ->method('addSharedInstanceWithParameters')
                    ->with($this->isInstanceOf($className), $className, $arguments);
                $instanceManager->expects($this->never())
                    ->method('addSharedInstance');
            } else {
                $instanceManager->expects($this->never())
                    ->method('addSharedInstanceWithParameters');
                $instanceManager->expects($this->once())
                    ->method('addSharedInstance')
                    ->with($this->isInstanceOf($className), $className);
            }
        } else {
            $instanceManager->expects($this->never())
                ->method('addSharedInstanceWithParameters');
            $instanceManager->expects($this->never())
                ->method('addSharedInstance');
        }

        $this->_model = new Magento_Di($definitions, $instanceManager);
    }

    /**
     * Invokes when DI class calls $this->get('<class_name>')
     *
     * @param $classOrAlias
     * @return PHPUnit_Framework_MockObject_MockObject|object
     */
    public function callbackGetSharedInstance($classOrAlias)
    {
        $this->_sharedInstances[$classOrAlias] = $this->getMock($classOrAlias, array(), array(), '', false);
        return $this->_sharedInstances[$classOrAlias];
    }

    /**
     * Assert test model object
     *
     * @param object $modelInstance
     * @param array $arguments
     */
    protected function _assertTestModel($modelInstance, array $arguments = array())
    {
        $this->assertInstanceOf(self::TEST_CLASS_MODEL, $modelInstance);

        foreach ($this->_propertyTypes[self::TEST_CLASS_MODEL] as $propertyName => $propertyClass) {
            $this->assertAttributeInstanceOf($propertyClass, $propertyName, $modelInstance);
        }
        $this->assertAttributeSame(null, '_resource', $modelInstance);
        $this->assertAttributeSame(null, '_resourceCollection', $modelInstance);
        $this->assertAttributeSame($arguments, '_data', $modelInstance);
    }

    /**
     * Assert test block object
     *
     * @param object $blockInstance
     * @param array $arguments
     */
    protected function _assertTestBlock($blockInstance, array $arguments = array())
    {
        $this->assertInstanceOf(self::TEST_CLASS_BLOCK, $blockInstance);

        foreach ($this->_propertyTypes[self::TEST_CLASS_BLOCK] as $propertyName => $propertyClass) {
            $this->assertAttributeInstanceOf($propertyClass, $propertyName, $blockInstance);
        }
        $this->assertAttributeSame($arguments, '_data', $blockInstance);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException Zend\Di\Exception\ClassNotFoundException
     * @expectedExceptionMessage Class (specified by alias Mage_Test_Model_TestStub) Mage_Test_Model_TestStub_Other could not be located in provided definitions.
     */
    // @codingStandardsIgnoreEnd
    public function testNewInstanceNoDefinitionException()
    {
        $this->_prepareMockForNewInstanceExceptions(true);
        $this->_model->newInstance(self::TEST_CLASS_MODEL);
    }

    /**
     * @expectedException Zend\Di\Exception\RuntimeException
     */
    public function testNewInstanceInvalidInstantiatorArray()
    {
        $this->_prepareMockForNewInstanceExceptions(false, array(self::TEST_CLASS_MODEL, 'testMethod'));
        $this->_model->newInstance(self::TEST_CLASS_MODEL);
    }

    /**
     * @expectedException Zend\Di\Exception\RuntimeException
     * @expectedExceptionMessage Invalid instantiator of type "string" for "Mage_Test_Model_TestStub".
     */
    public function testNewInstanceInvalidInstantiatorNotArray()
    {
        $this->_prepareMockForNewInstanceExceptions(false, 'test string');
        $this->_model->newInstance(self::TEST_CLASS_MODEL);
    }

    /**
     * Prepares all mocks for tests with exceptions
     *
     * @param bool $noDefinition
     * @param string|array $invalidInstantiator
     */
    protected function _prepareMockForNewInstanceExceptions($noDefinition = false, $invalidInstantiator = null)
    {
        $definitions = $this->getMock(
            'Zend\Di\DefinitionList', array('hasClass', 'getInstantiator'), array(), '', false
        );
        if ($noDefinition) {
            $definitions->expects($this->exactly(2))
                ->method('hasClass')
                ->will($this->returnCallback(array($this, 'callbackHasClassForExceptions')));
        } elseif ($invalidInstantiator) {
            $definitions->expects($this->exactly(2))
                ->method('hasClass')
                ->will($this->returnValue(true));
            $definitions->expects($this->once())
                ->method('getInstantiator')
                ->will($this->returnValue($invalidInstantiator));
        }

        $instanceManager = $this->getMock('Zend\Di\InstanceManager', array('hasAlias', 'getClassFromAlias'));
        $instanceManager->expects($this->once())
            ->method('hasAlias')
            ->will($this->returnValue($noDefinition));
        if ($noDefinition) {
            $instanceManager->expects($this->once())
                ->method('getClassFromAlias')
                ->will($this->returnValue(self::TEST_CLASS_MODEL . '_Other'));
        }

        $this->_model = new Magento_Di($definitions, $instanceManager);
    }

    /**
     * @param string $className
     * @return bool
     */
    public function callbackHasClassForExceptions($className)
    {
        return $className == self::TEST_CLASS_MODEL;
    }

    /**
     * Data provider for testNewInstanceWithDefinitionsWithoutResolve
     *
     * @return array
     */
    public function newInstanceWithDefinitionsWithoutResolveDataProvider()
    {
        $testClassOther = self::TEST_CLASS_OTHER;

        return array(
            'shared with arguments' => array(
                '$instantiator' => self::CONSTRUCT_METHOD,
                '$className'    => self::TEST_CLASS_OTHER,
                '$arguments'    => array(2 => 'test string'),
                '$isShared'     => true,
            ),
            'shared without arguments' => array(
                '$instantiator' => self::CONSTRUCT_METHOD,
                '$className'    => self::TEST_CLASS_OTHER,
                '$arguments'    => array(),
                '$isShared'     => true,
            ),
            'not shared' => array(
                '$instantiator' => self::CONSTRUCT_METHOD,
                '$className'    => self::TEST_CLASS_OTHER,
                '$arguments'    => array(),
                '$isShared'     => false,
            ),
            'not shared callback' => array(
                '$instantiator' => array(new $testClassOther(), 'setOrigData'), // setOrigData returns object itself
                '$className'    => self::TEST_CLASS_OTHER,
                '$arguments'    => array(),
                '$isShared'     => false,
            ),
        );
    }

    /**
     * @param string|array $instantiator
     * @param string $className
     * @param array $arguments
     * @param bool $isShared
     *
     * @dataProvider newInstanceWithDefinitionsWithoutResolveDataProvider
     */
    public function testNewInstanceWithDefinitionsWithoutResolve(
        $instantiator, $className, array $arguments = array(), $isShared = true
    ) {
        $this->_prepareMockForNewInstanceWithDefinitionsWithoutResolve(
            $instantiator, $className, $arguments, $isShared
        );

        $testObject = $this->_model->newInstance($className, $arguments, $isShared);
        $this->assertInstanceOf($className, $testObject);
        $this->assertAttributeEmpty('instanceContext', $this->_model);
    }

    /**
     * Prepares all mocks for testNewInstanceWithDefinitionsWithoutResolve
     *
     * @param string|array $instantiator
     * @param string $className
     * @param array $arguments
     * @param bool $isShared
     */
    protected function _prepareMockForNewInstanceWithDefinitionsWithoutResolve(
        $instantiator, $className, array $arguments = array(), $isShared = true
    ) {
        $definitions = $this->getMock(
            'Zend\Di\DefinitionList', array('hasClass', 'getInstantiator', 'hasMethodParameters', 'hasMethod'),
            array(), '', false
        );
        $definitions->expects($this->exactly(2))
            ->method('hasClass')
            ->will($this->returnValue(true));
        $definitions->expects($this->once())
            ->method('getInstantiator')
            ->will($this->returnValue($instantiator));

        if (is_array($instantiator)) {
            $definitions->expects($this->never())
                ->method('hasMethodParameters');
            $definitions->expects($this->once())
                ->method('hasMethod')
                ->with(get_class($instantiator[0]), $instantiator[1])
                ->will($this->returnValue(false));
        } else {
            $definitions->expects($this->once())
                ->method('hasMethodParameters')
                ->will($this->returnValue(false));
        }

        $instanceManager = $this->getMock(
            'Zend\Di\InstanceManager', array('hasAlias', 'addSharedInstanceWithParameters', 'addSharedInstance')
        );
        $instanceManager->expects($this->any())
            ->method('hasAlias')
            ->will($this->returnValue(false));

        if ($isShared) {
            if ($arguments) {
                $instanceManager->expects($this->once())
                    ->method('addSharedInstanceWithParameters')
                    ->with($this->isInstanceOf($className), $className, $arguments);
                $instanceManager->expects($this->never())
                    ->method('addSharedInstance');
            } else {
                $instanceManager->expects($this->never())
                    ->method('addSharedInstanceWithParameters');
                $instanceManager->expects($this->once())
                    ->method('addSharedInstance')
                    ->with($this->isInstanceOf($className), $className);
            }
        } else {
            $instanceManager->expects($this->never())
                ->method('addSharedInstanceWithParameters');
            $instanceManager->expects($this->never())
                ->method('addSharedInstance');
        }

        $this->_model = new Magento_Di($definitions, $instanceManager);
    }

    /**
     * Data provider for testNewInstanceWithDefinitionsWithResolve
     *
     * @return array
     */
    public function testNewInstanceWithDefinitionsWithResolveDataProvider()
    {
        return array(
            'model with data in new array format' => array(
                '$arguments' => array('data' => $this->_expectedDataValue)
            ),
            'model with data in new numeric format' => array(
                '$arguments' => array(4 => $this->_expectedDataValue)
            ),
            'model with data in old format' => array(
                '$arguments' => $this->_expectedDataValue
            ),
        );
    }

    /**
     * @param array $arguments
     *
     * @dataProvider testNewInstanceWithDefinitionsWithResolveDataProvider
     */
    public function testNewInstanceWithDefinitionsWithResolve(array $arguments)
    {
        $className  = self::TEST_CLASS_MODEL . '_Alias';
        $classAlias = self::TEST_CLASS_MODEL;
        $this->_prepareMockForNewInstanceWithDefinitionsWithResolve($classAlias);

        $testModel = $this->_model->newInstance($className, $arguments, false);
        $this->assertInstanceOf($classAlias, $testModel);
        $this->assertAttributeEquals($this->_expectedDataValue, '_data', $testModel);
    }

    /**
     * Prepares all mocks for testNewInstanceWithDefinitionsWithResolve
     *
     * @param string $className
     */
    protected function _prepareMockForNewInstanceWithDefinitionsWithResolve($className)
    {
        $definitions = $this->getMock(
            'Zend\Di\DefinitionList',
            array(
                'hasClass',
                'getInstantiator',
                'hasMethodParameters',
                'getMethodParameters'
            ),
            array(), '', false
        );
        $definitions->expects($this->any())
            ->method('hasClass')
            ->will($this->returnValue(true));
        $definitions->expects($this->any())
            ->method('getInstantiator')
            ->will($this->returnValue('__construct'));

        $definitions->expects($this->any())
            ->method('hasMethodParameters')
            ->with($className, self::CONSTRUCT_METHOD)
            ->will($this->returnValue(true));

        $constructParameters = array();
        foreach ($this->_constructParameters[$className] as $key => $data) {
            $key = $className . $key;
            $constructParameters[$key] = $data;
        }
        $definitions->expects($this->once())
            ->method('getMethodParameters')
            ->with($className, self::CONSTRUCT_METHOD)
            ->will($this->returnValue($constructParameters));

        $instanceManager = $this->getMock(
            'Zend\Di\InstanceManager', array('hasAlias', 'getClassFromAlias', 'hasSharedInstance', 'getSharedInstance')
        );
        $instanceManager->expects($this->any())
            ->method('hasAlias')
            ->will($this->returnValue(true));
        $instanceManager->expects($this->any())
            ->method('getClassFromAlias')
            ->will($this->returnValue($className));
        $instanceManager->expects($this->any())
            ->method('hasSharedInstance')
            ->will($this->returnValue(true));
        $instanceManager->expects($this->any())
            ->method('getSharedInstance')
            ->will($this->returnCallback(array($this, 'callbackGetSharedInstance')));

        $this->_model = new Magento_Di($definitions, $instanceManager);
    }
}

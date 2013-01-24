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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_ObjectManager_Zend
 */
class Magento_ObjectManager_ZendTest extends PHPUnit_Framework_TestCase
{
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
     * @var Magento_Di_Zend|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_diInstance;

    protected function tearDown()
    {
        unset($this->_objectManager);
        unset($this->_magentoConfig);
        unset($this->_instanceManager);
        unset($this->_diInstance);
    }

    public function testConstructWithDiObject()
    {
        $diInstance = $this->getMock('Magento_Di_Zend',
            array('instanceManager')
        );
        $instanceManager = $this->getMock('Magento_Di_InstanceManager_Zend', array('addSharedInstance'),
            array(), '', false
        );
        $diInstance->expects($this->once())
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));
        $instanceManager->expects($this->once())
            ->method('addSharedInstance')
            ->will($this->returnCallback(array($this, 'verifyAddSharedInstanceCallback')));

        $model = new Magento_ObjectManager_Zend(null, $diInstance);
        $this->assertAttributeInstanceOf(get_class($diInstance), '_di', $model);
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

        $this->_instanceManager = $this->getMock('Magento_Di_InstanceManager_Zend', array('addSharedInstance'),
            array(), '', false
        );
        $this->_diInstance = $this->getMock('Magento_Di_Zend',
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
     * Callback method for Magento_Di_Zend::newInstance
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
     * Callback method for Magento_Di_Zend::get
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

    public function testAddSharedInstance()
    {
        $object = new Varien_Object();
        $alias  = 'Varien_Object_Alias';

        $this->_prepareObjectManagerForAddSharedInstance($object, $alias);
        $this->_objectManager->addSharedInstance($object, $alias);
    }

    /**
     * Prepare all required mocks for addSharedInstance
     *
     * @param object $instance
     * @param string $classOrAlias
     */
    protected function _prepareObjectManagerForAddSharedInstance($instance, $classOrAlias)
    {
        $diInstance      = $this->getMock('Magento_Di_Zend', array('instanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Di_InstanceManager_Zend', array('addSharedInstance'), array(), '', false
        );

        $instanceManager->expects($this->exactly(2))
            ->method('addSharedInstance');
        $instanceManager->expects($this->at(1))
            ->method('addSharedInstance')
            ->with($instance, $classOrAlias);
        $diInstance->expects($this->exactly(2))
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));

        $this->_objectManager = new Magento_ObjectManager_Zend(null, $diInstance);
    }

    public function testRemoveSharedInstance()
    {
        $alias = 'Varien_Object_Alias';

        $this->_prepareObjectManagerForRemoveSharedInstance($alias);
        $this->_objectManager->removeSharedInstance($alias);
    }

    /**
     * Prepare all required mocks for removeSharedInstance
     *
     * @param string $classOrAlias
     */
    protected function _prepareObjectManagerForRemoveSharedInstance($classOrAlias)
    {
        $diInstance      = $this->getMock('Magento_Di_Zend', array('instanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Di_InstanceManager_Zend', array('addSharedInstance', 'removeSharedInstance'), array(), '',
            false
        );

        $instanceManager->expects($this->any())
            ->method('addSharedInstance')
            ->will($this->returnSelf());
        $instanceManager->expects($this->once())
            ->method('removeSharedInstance')
            ->with($classOrAlias);
        $diInstance->expects($this->exactly(2))
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));

        $this->_objectManager = new Magento_ObjectManager_Zend(null, $diInstance);
    }

    public function testHasSharedInstance()
    {
        $alias = 'Varien_Object_Alias';

        $this->_prepareObjectManagerForHasSharedInstance($alias);
        $this->_objectManager->hasSharedInstance($alias);
    }

    /**
     * Prepare all required mocks for hasSharedInstance
     *
     * @param string $classOrAlias
     */
    protected function _prepareObjectManagerForHasSharedInstance($classOrAlias)
    {
        $diInstance      = $this->getMock('Magento_Di_Zend', array('instanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Di_InstanceManager_Zend', array('hasSharedInstance'), array(), '',
            false
        );
        $instanceManager->expects($this->once())
            ->method('hasSharedInstance')
            ->with($classOrAlias);
        $diInstance->expects($this->exactly(2))
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));

        $this->_objectManager = new Magento_ObjectManager_Zend(null, $diInstance);
    }

    public function testAddAlias()
    {
        $alias = 'Varien_Object_Alias';

        $diInstance      = $this->getMock('Magento_Di_Zend', array('instanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Di_InstanceManager_Zend', array('addSharedInstance', 'removeSharedInstance', 'addAlias'),
            array(), '', false
        );

        $diInstance->expects($this->any())
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));
        $instanceManager->expects($this->any())
            ->method('addSharedInstance')
            ->will($this->returnSelf());

        $this->_objectManager = new Magento_ObjectManager_Zend(null, $diInstance);

        $instanceManager->expects($this->once())
            ->method('addAlias')
            ->with($alias, self::CLASS_NAME, array());

        $this->_objectManager->addAlias($alias, self::CLASS_NAME);
    }

    public function testGetClassFromAlias()
    {
        $alias = 'Varien_Object_Alias';

        $diInstance      = $this->getMock('Magento_Di_Zend', array('instanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Di_InstanceManager_Zend', array('addSharedInstance', 'removeSharedInstance', 'getClassFromAlias'),
            array(), '', false
        );

        $diInstance->expects($this->any())
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));
        $instanceManager->expects($this->any())
            ->method('addSharedInstance')
            ->will($this->returnSelf());

        $this->_objectManager = new Magento_ObjectManager_Zend(null, $diInstance);

        $instanceManager->expects($this->once())
            ->method('getClassFromAlias')
            ->with($alias);

        $this->_objectManager->getClassFromAlias($alias);
    }
}

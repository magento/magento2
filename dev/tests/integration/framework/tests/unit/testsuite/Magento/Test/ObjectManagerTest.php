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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_ObjectManager_Zend
 */
class Magento_Test_ObjectManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test resource value
     */
    const TEST_RESOURCE = 'test_resource';

    /**
     * ObjectManager instance for tests
     *
     * @var Magento_Test_ObjectManager
     */
    protected $_model;

    /**
     * Expected instance manager parametrized cache after clear
     *
     * @var array
     */
    protected $_instanceCache = array(
        'hashShort' => array(),
        'hashLong'  => array()
    );

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testClearCache()
    {
        $this->_prepareObjectManagerForClearCache();
        $this->_model->clearCache();
    }

    /**
     * Prepare all required mocks for clearCache
     */
    protected function _prepareObjectManagerForClearCache()
    {
        $diInstance      = $this->getMock('Zend\Di\Di', array('get', 'instanceManager', 'setInstanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Test_Di_InstanceManager', array('addSharedInstance'), array(), '', false
        );

        $diInstance->expects($this->exactly(3))
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));
        $diInstance->expects($this->once())
            ->method('get')
            ->with('Mage_Core_Model_Resource')
            ->will($this->returnValue(self::TEST_RESOURCE));
        $diInstance->expects($this->exactly(2))
            ->method('setInstanceManager')
            ->will($this->returnCallback(array($this, 'verifySetInstanceManager')));

        $this->_model = new Magento_Test_ObjectManager(null, $diInstance);

        $instanceManager->expects($this->exactly(2))
            ->method('addSharedInstance');
        $instanceManager->expects($this->at(0))
            ->method('addSharedInstance')
            ->with($this->_model, 'Magento_ObjectManager');
        $instanceManager->expects($this->at(1))
            ->method('addSharedInstance')
            ->with(self::TEST_RESOURCE, 'Mage_Core_Model_Resource');
    }

    /**
     * Callback method for Zend\Di\Di::setInstanceManager
     *
     * @param \Zend\Di\InstanceManager $instanceManager
     */
    public function verifySetInstanceManager($instanceManager)
    {
        $this->assertInstanceOf('Magento_Test_Di_InstanceManager', $instanceManager);
        $this->assertAttributeEmpty('sharedInstances', $instanceManager);
        $this->assertAttributeEquals($this->_instanceCache, 'sharedInstancesWithParams', $instanceManager);
    }

    public function testAddSharedInstance()
    {
        $object = new Varien_Object();
        $alias  = 'Varien_Object_Alias';

        $this->_prepareObjectManagerForAddSharedInstance($object, $alias);
        $this->_model->addSharedInstance($object, $alias);
    }

    /**
     * Prepare all required mocks for addSharedInstance
     *
     * @param object $instance
     * @param string $classOrAlias
     */
    protected function _prepareObjectManagerForAddSharedInstance($instance, $classOrAlias)
    {
        $diInstance      = $this->getMock('Zend\Di\Di', array('instanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Test_Di_InstanceManager', array('addSharedInstance'), array(), '', false
        );

        $instanceManager->expects($this->exactly(2))
            ->method('addSharedInstance');
        $instanceManager->expects($this->at(1))
            ->method('addSharedInstance')
            ->with($instance, $classOrAlias);
        $diInstance->expects($this->exactly(2))
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));

        $this->_model = new Magento_Test_ObjectManager(null, $diInstance);
    }

    public function testRemoveSharedInstance()
    {
        $alias = 'Varien_Object_Alias';

        $this->_prepareObjectManagerForRemoveSharedInstance($alias);
        $this->_model->removeSharedInstance($alias);
    }

    /**
     * Prepare all required mocks for removeSharedInstance
     *
     * @param string $classOrAlias
     */
    protected function _prepareObjectManagerForRemoveSharedInstance($classOrAlias)
    {
        $diInstance      = $this->getMock('Zend\Di\Di', array('instanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Test_Di_InstanceManager', array('removeSharedInstance'), array(), '', false
        );

        $instanceManager->expects($this->once())
            ->method('removeSharedInstance')
            ->with($classOrAlias);
        $diInstance->expects($this->exactly(2))
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));

        $this->_model = new Magento_Test_ObjectManager(null, $diInstance);
    }
}

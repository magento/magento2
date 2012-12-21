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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * ObjectManager mock for tests
     *
     * @var Magento_Test_ObjectManager
     */
    protected $_model;

    /**
     * List of classes to call __destruct() on
     *
     * @var array
     */
    protected $_classesToDestruct = array();

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
        unset($this->_classesToDestruct);
    }

    /**
     * @param bool $shared
     * @dataProvider clearCacheDataProvider
     */
    public function testClearCache($shared)
    {
        $this->_prepareObjectManagerForClearCache($shared);
        $this->_model->clearCache();
    }

    /**
     * @return array
     */
    public function clearCacheDataProvider()
    {
        return array(
            'noSharedInstances' => array(false),
            'withSharedInstances' => array(true),
        );
    }

    /**
     * Prepare all required mocks for clearCache
     * @param $shared
     */
    protected function _prepareObjectManagerForClearCache($shared)
    {
        $diInstance      = $this->getMock('Magento_Di_Zend', array('get', 'instanceManager', 'setInstanceManager'));
        $instanceManager = $this->getMock(
            'Magento_Di_InstanceManager_Zend', array('addSharedInstance', 'hasSharedInstance'), array(), '', false
        );

        $diInstance->expects($this->exactly(7))
            ->method('instanceManager')
            ->will($this->returnValue($instanceManager));

        $instanceManager->expects($this->any())
            ->method('hasSharedInstance')
            ->will($this->returnValue($shared));

        $getCallCount = $shared ? 5 : 1;
        $diInstance->expects($this->exactly($getCallCount))
            ->method('get')
            ->will($this->returnCallback(array($this, 'getCallback')));
        $diInstance->expects($this->any())
            ->method('setInstanceManager')
            ->will($this->returnSelf());

        $this->_model = new Magento_Test_ObjectManager(null, $diInstance);

        $instanceManager->expects($this->exactly(2))
            ->method('addSharedInstance');
        $instanceManager->expects($this->at(4))
            ->method('addSharedInstance')
            ->with($this->_model, 'Magento_ObjectManager');
        $instanceManager->expects($this->at(5))
            ->method('addSharedInstance')
            ->with(self::TEST_RESOURCE, 'Mage_Core_Model_Resource');
    }

    /**
     * Callback method for Magento_Di_Zend::get
     *
     * @param string $className
     * @return PHPUnit_Framework_MockObject_MockObject|string
     */
    public function getCallback($className)
    {
        if ($className != 'Mage_Core_Model_Resource') {
            $this->_classesToDestruct[] = $className;
            $mock = $this->getMock($className, array('__destruct'), array(), '', false);
            $mock->expects($this->once())
                ->method('__destruct');
            return $mock;
        } else {
            return self::TEST_RESOURCE;
        }
    }
}

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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_TestCase_ObjectManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * List of block default dependencies
     *
     * @var array
     */
    protected $_blockDependencies = array(
        'request'         => 'Mage_Core_Controller_Request_Http',
        'layout'          => 'Mage_Core_Model_Layout',
        'eventManager'    => 'Mage_Core_Model_Event_Manager',
        'translator'      => 'Mage_Core_Model_Translate',
        'cache'           => 'Mage_Core_Model_CacheInterface',
        'designPackage'   => 'Mage_Core_Model_Design_Package',
        'session'         => 'Mage_Core_Model_Session',
        'storeConfig'     => 'Mage_Core_Model_Store_Config',
        'frontController' => 'Mage_Core_Controller_Varien_Front'
    );

    /**
     * List of model default dependencies
     *
     * @var array
     */
    protected $_modelDependencies = array(
        'eventDispatcher'    => 'Mage_Core_Model_Event_Manager',
        'cacheManager'       => 'Mage_Core_Model_CacheInterface',
        'resource'           => 'Mage_Core_Model_Resource_Abstract',
        'resourceCollection' => 'Varien_Data_Collection_Db'
    );

    /**
     * @covers Magento_Test_TestCase_ObjectManager::getBlock
     */
    public function testGetBlock()
    {
        $objectManager = new Magento_Test_Helper_ObjectManager($this);
        /** @var $template Mage_Core_Block_Template */
        $template = $objectManager->getObject('Mage_Core_Block_Template');
        $this->assertInstanceOf('Mage_Core_Block_Template', $template);
        foreach ($this->_blockDependencies as $propertyName => $propertyType) {
            $this->assertAttributeInstanceOf($propertyType, '_' . $propertyName, $template);
        }

        $area = 'frontend';
        /** @var $layoutMock Mage_Core_Model_Layout */
        $layoutMock = $this->getMock('Mage_Core_Model_Layout', array('getArea'), array(), '', false);
        $layoutMock->expects($this->once())
            ->method('getArea')
            ->will($this->returnValue($area));

        $arguments = array('layout' => $layoutMock);
        /** @var $template Mage_Core_Block_Template */
        $template = $objectManager->getObject('Mage_Core_Block_Template', $arguments);
        $this->assertEquals($area, $template->getArea());
    }

    /**
     * @covers Magento_Test_TestCase_ObjectManager::getModel
     */
    public function testGetModel()
    {
        $objectManager = new Magento_Test_Helper_ObjectManager($this);
        /** @var $model Mage_Core_Model_Config_Data */
        $model = $objectManager->getObject('Mage_Core_Model_Config_Data');
        $this->assertInstanceOf('Mage_Core_Model_Config_Data', $model);
        foreach ($this->_modelDependencies as $propertyName => $propertyType) {
            $this->assertAttributeInstanceOf($propertyType, '_' . $propertyName, $model);
        }

        /** @var $resourceMock Mage_Core_Model_Resource_Resource */
        $resourceMock = $this->getMock('Mage_Core_Model_Resource_Resource', array('_getReadAdapter', 'getIdFieldName'),
            array(), '', false
        );
        $resourceMock->expects($this->once())
            ->method('_getReadAdapter')
            ->will($this->returnValue(false));
        $resourceMock->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));
        $arguments = array('resource' => $resourceMock);
        $model = $objectManager->getObject('Mage_Core_Model_Config_Data', $arguments);
        $this->assertFalse($model->getResource()->getDataVersion('test'));
    }
}

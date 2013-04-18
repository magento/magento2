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
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Config_Storage
 */
class Mage_Core_Model_Config_StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Storage
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourcesConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loaderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Mage_Core_Model_ConfigInterface',
            array(), array(), '', false, false);
        $this->_resourcesConfigMock = $this->getMock('Mage_Core_Model_Config_Resource',
            array(), array(), '', false, false);
        $this->_cacheMock = $this->getMock('Mage_Core_Model_Config_Cache',
            array(), array(), '', false, false);
        $this->_loaderMock = $this->getMock('Mage_Core_Model_Config_LoaderInterface',
            array(), array(), '', false, false);
        $this->_factoryMock = $this->getMock('Mage_Core_Model_Config_BaseFactory',
            array(), array(), '', false, false);
        $this->_model = new Mage_Core_Model_Config_Storage($this->_cacheMock, $this->_loaderMock, $this->_factoryMock,
            $this->_resourcesConfigMock);
    }

    protected function tearDown()
    {
        unset($this->_resourcesConfigMock);
        unset($this->_cacheMock);
        unset($this->_loaderMock);
        unset($this->_factoryMock);
        unset($this->_model);
    }

    public function testGetConfigurationWithData()
    {
        $this->_cacheMock->expects($this->once())->method('load')->will($this->returnValue($this->_configMock));
        $this->_factoryMock->expects($this->never())->method('create');
        $this->_loaderMock->expects($this->never())->method('load');
        $this->_cacheMock->expects($this->never())->method('save');
        $this->_resourcesConfigMock->expects($this->once())
            ->method('setConfig')
            ->with($this->equalTo($this->_configMock));
        $this->_model->getConfiguration();
    }

    public function testGetConfigurationWithoutData()
    {
        $mockConfigBase = $this->getMockBuilder('Mage_Core_Model_Config_Base')->disableOriginalConstructor()->getMock();
        $this->_cacheMock->expects($this->once())->method('load')->will($this->returnValue(false));
        $this->_factoryMock->expects($this->once())->method('create')->will($this->returnValue($mockConfigBase));
        $this->_loaderMock->expects($this->once())->method('load');
        $this->_cacheMock->expects($this->once())->method('save');
        $this->_resourcesConfigMock->expects($this->once())
            ->method('setConfig')
            ->with($this->equalTo($mockConfigBase));
        $this->_model->getConfiguration();
    }

    public function testRemoveCache()
    {
        $this->_cacheMock->expects($this->once())->method('clean');
        $this->_model->removeCache();
    }

}
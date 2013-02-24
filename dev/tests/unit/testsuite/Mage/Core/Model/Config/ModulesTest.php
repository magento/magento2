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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Config_ModulesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Modules
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageMock;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Mage_Core_Model_ConfigInterface');
        $this->_storageMock = $this->getMock('Mage_Core_Model_Config_StorageInterface');
        $this->_storageMock->expects($this->any())->method('getConfiguration')
            ->will($this->returnValue($this->_configMock));
        $this->_model = new Mage_Core_Model_Config_Modules($this->_storageMock);
    }

    protected function tearDown()
    {
        unset($this->_configMock);
        unset($this->_storageMock);
        unset($this->_model);
    }

    public function testGetNode()
    {
        $path = 'some_path';
        $result = 'some_result';
        $this->_configMock->expects($this->once())->method('getNode')->with($path)->will($this->returnValue($result));
        $this->assertEquals($result, $this->_model->getNode($path));
    }

    public function testSetNode()
    {
        $path = 'some_path';
        $value = 'some_value';
        $this->_configMock->expects($this->once())->method('setNode')
            ->with($path, $value, true);
        $this->_model->setNode($path, $value);
    }

    public function testGetXpath()
    {
        $path = 'some_path';
        $result = 'some_result';
        $this->_configMock->expects($this->once())->method('getXpath')->with($path)->will($this->returnValue($result));
        $this->assertEquals($result, $this->_model->getXpath($path));
    }

    public function testGetModuleConfigReturnsRequestedModuleConfig()
    {
        $this->_prepareModulesConfig();
        $this->assertEquals('backend', $this->_model->getModuleConfig('backend'));
    }

    public function testGetModuleConfigReturnsAllModulesConfigIfNoModuleIsSpecified()
    {
        $modulesConfig = $this->_prepareModulesConfig();
        $this->assertEquals($modulesConfig, $this->_model->getModuleConfig());
    }

    public function _prepareModulesConfig()
    {
        $modulesConfig = new stdClass();
        $modulesConfig->core = 'core';
        $modulesConfig->backend = 'backend';
        $this->_configMock->expects($this->once())->method('getNode')->with('modules')
            ->will($this->returnValue($modulesConfig));
        return $modulesConfig;
    }
}

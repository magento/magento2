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

class Mage_Core_Model_ModuleManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * XPath in the configuration of a module output flag
     */
    const XML_PATH_OUTPUT_ENABLED = 'custom/is_module_output_enabled';

    /**
     * @var Mage_Core_Model_ModuleManager
     */
    private $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_config;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_storeConfig;

    protected function setUp()
    {
        $configXml = new SimpleXMLElement('<?xml version="1.0"?>
            <config>
                <modules>
                    <Module_EnabledOne><active>1</active></Module_EnabledOne>
                    <Module_EnabledTwo><active>true</active></Module_EnabledTwo>
                    <Module_DisabledOne><active>0</active></Module_DisabledOne>
                    <Module_DisabledTwo><active>false</active></Module_DisabledTwo>
                    <Module_DisabledOutputOne><active>true</active></Module_DisabledOutputOne>
                    <Module_DisabledOutputTwo><active>true</active></Module_DisabledOutputTwo>
                </modules>
            </config>
        ');
        $this->_config = $this->getMockForAbstractClass('Mage_Core_Model_ConfigInterface');
        $this->_config
            ->expects($this->any())
            ->method('getNode')
            ->will($this->returnCallback(
                function ($path) use ($configXml) {
                    $nodes = $configXml->xpath($path);
                    return reset($nodes);
                }
            ))
        ;
        $this->_storeConfig = $this->getMockForAbstractClass('Mage_Core_Model_Store_ConfigInterface');
        $this->_model = new Mage_Core_Model_ModuleManager($this->_config, $this->_storeConfig, array(
            'Module_DisabledOutputOne' => self::XML_PATH_OUTPUT_ENABLED,
            'Module_DisabledOutputTwo' => 'Mage_Core_Model_ModuleManagerTest::XML_PATH_OUTPUT_ENABLED',
        ));
    }

    /**
     * @param string $moduleName
     * @param bool $expectedResult
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($moduleName, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->isEnabled($moduleName));
    }

    public function isEnabledDataProvider()
    {
        return array(
            'enabled module, int status'    => array('Module_EnabledOne', true),
            'enabled module, bool status'   => array('Module_EnabledTwo', true),
            'disabled module, int status'   => array('Module_DisabledOne', false),
            'disabled module, bool status'  => array('Module_DisabledTwo', false),
            'unknown module, no status'     => array('Module_Unknown', false),
        );
    }

    /**
     * @param string $moduleName
     * @param bool $expectedResult
     * @dataProvider isEnabledDataProvider
     */
    public function testIsOutputEnabledModuleStatus($moduleName, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled($moduleName));
    }

    /**
     * @param bool $configValue
     * @param bool $expectedResult
     * @dataProvider isOutputEnabledGenericConfigPathDataProvider
     */
    public function testIsOutputEnabledGenericConfigPath($configValue, $expectedResult)
    {
        $this->_storeConfig
            ->expects($this->once())
            ->method('getConfigFlag')
            ->with('advanced/modules_disable_output/Module_EnabledOne')
            ->will($this->returnValue($configValue))
        ;
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_EnabledOne'));
    }

    public function isOutputEnabledGenericConfigPathDataProvider()
    {
        return array(
            'output disabled'   => array(true, false),
            'output enabled'    => array(false, true),
        );
    }

    /**
     * @param bool $configValue
     * @param string $moduleName
     * @param bool $expectedResult
     * @dataProvider isOutputEnabledCustomConfigPathDataProvider
     */
    public function testIsOutputEnabledCustomConfigPath($configValue, $moduleName, $expectedResult)
    {
        $this->_storeConfig
            ->expects($this->at(0))
            ->method('getConfigFlag')
            ->with(self::XML_PATH_OUTPUT_ENABLED)
            ->will($this->returnValue($configValue))
        ;
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled($moduleName));
    }

    public function isOutputEnabledCustomConfigPathDataProvider()
    {
        return array(
            'path literal, output disabled'     => array(false, 'Module_DisabledOutputOne', false),
            'path literal, output enabled'      => array(true, 'Module_DisabledOutputOne', true),
            'path constant, output disabled'    => array(false, 'Module_DisabledOutputTwo', false),
            'path constant, output enabled'     => array(true, 'Module_DisabledOutputTwo', true),
        );
    }
}

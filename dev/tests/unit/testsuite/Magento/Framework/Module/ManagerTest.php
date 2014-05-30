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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Module;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * XPath in the configuration of a module output flag
     */
    const XML_PATH_OUTPUT_ENABLED = 'custom/is_module_output_enabled';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_moduleList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_outputConfig;

    protected function setUp()
    {
        $this->_moduleList = $this->getMockForAbstractClass('Magento\Framework\Module\ModuleListInterface');
        $this->_outputConfig = $this->getMockForAbstractClass('Magento\Framework\Module\Output\ConfigInterface');
        $this->_model = new \Magento\Framework\Module\Manager(
            $this->_outputConfig,
            $this->_moduleList,
            array(
                'Fixture_Module' => self::XML_PATH_OUTPUT_ENABLED,
            )
        );
    }

    public function testIsEnabledReturnsTrueForActiveModule()
    {
        $this->_moduleList->expects(
            $this->once()
        )->method(
            'getModule'
        )->will(
            $this->returnValue(array('name' => 'Some_Module'))
        );
        $this->assertTrue($this->_model->isEnabled('Some_Module'));
    }

    public function testIsEnabledReturnsFalseForInactiveModule()
    {
        $this->_moduleList->expects($this->once())->method('getModule');
        $this->assertFalse($this->_model->isEnabled('Some_Module'));
    }

    public function testIsOutputEnabledReturnsFalseForDisabledModule()
    {
        $this->_outputConfig->expects($this->any())->method('isSetFlag')->will($this->returnValue(true));
        $this->assertFalse($this->_model->isOutputEnabled('Nonexisting_Module'));
    }

    /**
     * @param bool $configValue
     * @param bool $expectedResult
     * @dataProvider isOutputEnabledGenericConfigPathDataProvider
     */
    public function testIsOutputEnabledGenericConfigPath($configValue, $expectedResult)
    {
        $this->_moduleList->expects(
            $this->any()
        )->method(
            'getModule'
        )->will(
            $this->returnValue(array('name' => 'Module_EnabledOne'))
        );
        $this->_outputConfig->expects(
            $this->once()
        )->method(
            'isEnabled'
        )->with(
            'Module_EnabledOne'
        )->will(
            $this->returnValue($configValue)
        );
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_EnabledOne'));
    }

    public function isOutputEnabledGenericConfigPathDataProvider()
    {
        return array('output disabled' => array(true, false), 'output enabled' => array(false, true));
    }

    /**
     * @param bool $configValue
     * @param string $moduleName
     * @param bool $expectedResult
     * @dataProvider isOutputEnabledCustomConfigPathDataProvider
     */
    public function testIsOutputEnabledCustomConfigPath($configValue, $moduleName, $expectedResult)
    {
        $this->_moduleList->expects(
            $this->any()
        )->method(
            'getModule'
        )->will(
            $this->returnValue(array('name' => $moduleName))
        );
        $this->_outputConfig->expects(
            $this->at(0)
        )->method(
            'isSetFlag'
        )->with(
            self::XML_PATH_OUTPUT_ENABLED
        )->will(
            $this->returnValue($configValue)
        );
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled($moduleName));
    }

    public function isOutputEnabledCustomConfigPathDataProvider()
    {
        return array(
            'path literal, output disabled' => array(false, 'Fixture_Module', false),
            'path literal, output enabled'  => array(true, 'Fixture_Module', true),
        );
    }
}

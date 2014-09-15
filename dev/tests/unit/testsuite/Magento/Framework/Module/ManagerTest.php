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

    /**
     * @var \Magento\Framework\Module\ResourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleResource;

    protected function setUp()
    {
        $this->_moduleList = $this->getMockForAbstractClass('Magento\Framework\Module\ModuleListInterface');
        $this->_moduleList->expects($this->any())
            ->method('getModule')
            ->will($this->returnValueMap([
                ['Module_One', ['name' => 'One_Module', 'schema_version' => '1']],
                ['Module_Two', ['name' => 'Two_Module', 'schema_version' => '2']],
                ['Module_Three', ['name' => 'Two_Three']],
            ]));
        $this->_outputConfig = $this->getMockForAbstractClass('Magento\Framework\Module\Output\ConfigInterface');
        $this->moduleResource = $this->getMockForAbstractClass('\Magento\Framework\Module\ResourceInterface');
        $this->_model = new \Magento\Framework\Module\Manager(
            $this->_outputConfig,
            $this->_moduleList,
            $this->moduleResource,
            array(
                'Module_Two' => self::XML_PATH_OUTPUT_ENABLED,
            )
        );
    }

    public function testIsEnabledReturnsTrueForActiveModule()
    {
        $this->assertTrue($this->_model->isEnabled('Module_One'));
    }

    public function testIsEnabledReturnsFalseForInactiveModule()
    {
        $this->assertFalse($this->_model->isEnabled('Disabled_Module'));
    }

    public function testIsOutputEnabledReturnsFalseForDisabledModule()
    {
        $this->_outputConfig->expects($this->any())->method('isSetFlag')->will($this->returnValue(true));
        $this->assertFalse($this->_model->isOutputEnabled('Disabled_Module'));
    }

    /**
     * @param bool $configValue
     * @param bool $expectedResult
     * @dataProvider isOutputEnabledGenericConfigPathDataProvider
     */
    public function testIsOutputEnabledGenericConfigPath($configValue, $expectedResult)
    {
        $this->_outputConfig->expects($this->once())
            ->method('isEnabled')
            ->with('Module_One')
            ->will($this->returnValue($configValue))
        ;
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_One'));
    }

    public function isOutputEnabledGenericConfigPathDataProvider()
    {
        return array('output disabled' => array(true, false), 'output enabled' => array(false, true));
    }

    /**
     * @param bool $configValue
     * @param bool $expectedResult
     * @dataProvider isOutputEnabledCustomConfigPathDataProvider
     */
    public function testIsOutputEnabledCustomConfigPath($configValue, $expectedResult)
    {
        $this->_outputConfig->expects($this->at(0))
            ->method('isSetFlag')
            ->with(self::XML_PATH_OUTPUT_ENABLED)
            ->will($this->returnValue($configValue))
        ;
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_Two'));
    }

    public function isOutputEnabledCustomConfigPathDataProvider()
    {
        return array(
            'path literal, output disabled' => array(false, false),
            'path literal, output enabled'  => array(true, true),
        );
    }

    /**
     * @param string $moduleName
     * @param string|bool $dbVersion
     * @param bool $expectedResult
     *
     * @dataProvider isDbUpToDateDataProvider
     */
    public function testIsDbSchemaUpToDate($moduleName, $dbVersion, $expectedResult)
    {
        $resourceName = 'resource';
        $this->moduleResource->expects($this->once())
            ->method('getDbVersion')
            ->with($resourceName)
            ->will($this->returnValue($dbVersion));
        $this->assertSame($expectedResult, $this->_model->isDbSchemaUpToDate($moduleName, $resourceName));
    }

    /**
     * @param string $moduleName
     * @param string|bool $dbVersion
     * @param bool $expectedResult
     *
     * @dataProvider isDbUpToDateDataProvider
     */
    public function testIsDbDataUpToDate($moduleName, $dbVersion, $expectedResult)
    {
        $resourceName = 'resource';
        $this->moduleResource->expects($this->once())
            ->method('getDataVersion')
            ->with($resourceName)
            ->will($this->returnValue($dbVersion));
        $this->assertSame($expectedResult, $this->_model->isDbDataUpToDate($moduleName, $resourceName));
    }

    /**
     * @return array
     */
    public function isDbUpToDateDataProvider()
    {
        return [
            'version in config == version in db' => ['Module_One', '1', true],
            'version in config < version in db' => ['Module_One', '2', false],
            'version in config > version in db' => ['Module_Two', '1', false],
            'no version in db' => ['Module_One', false, false],
        ];
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Schema version for module 'Module_Three' is not specified
     */
    public function testIsDbSchemaUpToDateException()
    {
        $this->_model->isDbSchemaUpToDate('Module_Three', 'resource');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Schema version for module 'Module_Three' is not specified
     */
    public function testIsDbDataUpToDateException()
    {
        $this->_model->isDbDataUpToDate('Module_Three', 'resource');
    }
}

<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Output\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /**
     * XPath in the configuration of a module output flag
     */
    const XML_PATH_OUTPUT_ENABLED = 'custom/is_module_output_enabled';

    /**
     * @var Manager
     */
    private $_model;

    /**
     * @var MockObject
     */
    private $_moduleList;

    /**
     * @var MockObject
     */
    private $_outputConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_moduleList = $this->getMockForAbstractClass(ModuleListInterface::class);
        $this->_moduleList->expects($this->any())
            ->method('getOne')
            ->will(
                $this->returnValueMap(
                    [
                        ['Module_One', ['name' => 'One_Module', 'setup_version' => '1']],
                        ['Module_Two', ['name' => 'Two_Module', 'setup_version' => '2']],
                        ['Module_Three', ['name' => 'Two_Three']],
                    ]
                )
            );
        $this->_outputConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->_model = new Manager(
            $this->_outputConfig,
            $this->_moduleList,
            [
                'Module_Two' => self::XML_PATH_OUTPUT_ENABLED,
            ]
        );
    }

    public function testIsEnabled()
    {
        $this->_moduleList->expects($this->exactly(2))->method('has')->will(
            $this->returnValueMap(
                [
                    ['Module_Exists', true],
                    ['Module_NotExists', false],
                ]
            )
        );
        $this->assertTrue($this->_model->isEnabled('Module_Exists'));
        $this->assertFalse($this->_model->isEnabled('Module_NotExists'));
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
        $this->_moduleList->expects($this->once())->method('has')->will($this->returnValue(true));
        $this->_outputConfig->expects($this->once())
            ->method('isEnabled')
            ->with('Module_One')
            ->will($this->returnValue($configValue));
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_One'));
    }

    /**
     * @return array
     */
    public function isOutputEnabledGenericConfigPathDataProvider()
    {
        return ['output disabled' => [true, false], 'output enabled' => [false, true]];
    }

    /**
     * @param bool $configValue
     * @param bool $expectedResult
     * @dataProvider isOutputEnabledCustomConfigPathDataProvider
     */
    public function testIsOutputEnabledCustomConfigPath($configValue, $expectedResult)
    {
        $this->_moduleList->expects($this->once())->method('has')->will($this->returnValue(true));
        $this->_outputConfig->expects($this->at(0))
            ->method('isSetFlag')
            ->with(self::XML_PATH_OUTPUT_ENABLED)
            ->will($this->returnValue($configValue));
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_Two'));
    }

    /**
     * @return array
     */
    public function isOutputEnabledCustomConfigPathDataProvider()
    {
        return [
            'path literal, output disabled' => [false, false],
            'path literal, output enabled'  => [true, true],
        ];
    }
}

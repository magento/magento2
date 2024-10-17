<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
            ->willReturnMap(
                [
                    ['Module_One', ['name' => 'One_Module', 'setup_version' => '1']],
                    ['Module_Two', ['name' => 'Two_Module', 'setup_version' => '2']],
                    ['Module_Three', ['name' => 'Two_Three']]
                ]
            );
        $this->_outputConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->_model = new Manager(
            $this->_outputConfig,
            $this->_moduleList,
            ['Module_Two' => self::XML_PATH_OUTPUT_ENABLED]
        );
    }

    /**
     * @return void
     */
    public function testIsEnabled(): void
    {
        $this->_moduleList->expects($this->exactly(2))->method('has')->willReturnMap(
            [
                ['Module_Exists', true],
                ['Module_NotExists', false]
            ]
        );
        $this->assertTrue($this->_model->isEnabled('Module_Exists'));
        $this->assertFalse($this->_model->isEnabled('Module_NotExists'));
    }

    /**
     * @return void
     */
    public function testIsOutputEnabledReturnsFalseForDisabledModule(): void
    {
        $this->_outputConfig->expects($this->any())->method('isSetFlag')->willReturn(true);
        $this->assertFalse($this->_model->isOutputEnabled('Disabled_Module'));
    }

    /**
     * @param bool $configValue
     * @param bool $expectedResult
     *
     * @return void
     * @dataProvider isOutputEnabledGenericConfigPathDataProvider
     */
    public function testIsOutputEnabledGenericConfigPath($configValue, $expectedResult): void
    {
        $this->_moduleList->expects($this->once())->method('has')->willReturn(true);
        $this->_outputConfig->expects($this->once())
            ->method('isEnabled')
            ->with('Module_One')
            ->willReturn($configValue);
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_One'));
    }

    /**
     * @return array
     */
    public static function isOutputEnabledGenericConfigPathDataProvider(): array
    {
        return ['output disabled' => [true, false], 'output enabled' => [false, true]];
    }

    /**
     * @param bool $configValue
     * @param bool $expectedResult
     *
     * @return void
     * @dataProvider isOutputEnabledCustomConfigPathDataProvider
     */
    public function testIsOutputEnabledCustomConfigPath($configValue, $expectedResult): void
    {
        $this->_moduleList->expects($this->once())->method('has')->willReturn(true);
        $this->_outputConfig
            ->method('isSetFlag')
            ->with(self::XML_PATH_OUTPUT_ENABLED)
            ->willReturn($configValue);
        $this->assertEquals($expectedResult, $this->_model->isOutputEnabled('Module_Two'));
    }

    /**
     * @return array
     */
    public static function isOutputEnabledCustomConfigPathDataProvider(): array
    {
        return [
            'path literal, output disabled' => [false, false],
            'path literal, output enabled'  => [true, true]
        ];
    }
}

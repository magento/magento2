<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Output\ConfigInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * XPath in the configuration of a module output flag
     * @deprecated
     */
    const XML_PATH_OUTPUT_ENABLED = 'custom/is_module_output_enabled';

    /**
     * @var Manager
     */
    private $model;

    /**
     * @var ModuleListInterface|Mock
     */
    private $moduleList;

    /**
     * @var ConfigInterface|Mock
     * @deprecated
     */
    private $outputConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->moduleList = $this->getMockBuilder(ModuleListInterface::class)
            ->getMockForAbstractClass();
        $this->outputConfig = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Manager(
            $this->outputConfig,
            $this->moduleList
        );
    }

    public function testIsEnabled()
    {
        $this->moduleList->expects($this->exactly(2))
            ->method('has')
            ->willReturnMap(
                [
                    ['Module_Exists', true],
                    ['Module_NotExists', false],
                ]
            );

        $this->assertTrue($this->model->isEnabled('Module_Exists'));
        $this->assertFalse($this->model->isEnabled('Module_NotExists'));
    }
}

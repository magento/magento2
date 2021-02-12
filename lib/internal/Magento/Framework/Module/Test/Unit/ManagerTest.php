<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
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
                    ['Module_Three', ['name' => 'Two_Three']],
                ]
            );

        $this->_model = new Manager(
            $this->_moduleList,
            [
                'Module_Two' => self::XML_PATH_OUTPUT_ENABLED,
            ]
        );
    }

    public function testIsEnabled()
    {
        $this->_moduleList->expects($this->exactly(2))->method('has')->willReturnMap(
            [
                ['Module_Exists', true],
                ['Module_NotExists', false],
            ]
        );
        $this->assertTrue($this->_model->isEnabled('Module_Exists'));
        $this->assertFalse($this->_model->isEnabled('Module_NotExists'));
    }

    public function testIsOutputEnabledReturnsFalseForDisabledModule()
    {
        $this->_moduleList->expects($this->once())->method('has')->willReturn(false);
        $this->assertFalse($this->_model->isOutputEnabled('Disabled_Module'));
    }
}

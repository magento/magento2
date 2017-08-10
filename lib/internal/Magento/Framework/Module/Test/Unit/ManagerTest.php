<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\Plugin\DbStatusValidator;

class ManagerTest extends \PHPUnit\Framework\TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->_moduleList = $this->getMockForAbstractClass(\Magento\Framework\Module\ModuleListInterface::class);
        $this->_moduleList->expects($this->any())
            ->method('getOne')
            ->will($this->returnValueMap([
                ['Module_One', ['name' => 'One_Module', 'setup_version' => '1']],
                ['Module_Two', ['name' => 'Two_Module', 'setup_version' => '2']],
                ['Module_Three', ['name' => 'Two_Three']],
            ]));
        $this->_outputConfig = $this->getMockForAbstractClass(\Magento\Framework\Module\Output\ConfigInterface::class);
        $this->_model = new \Magento\Framework\Module\Manager(
            $this->_outputConfig,
            $this->_moduleList,
            [
                'Module_Two' => self::XML_PATH_OUTPUT_ENABLED,
            ]
        );
    }

    public function testIsEnabled()
    {
        $this->_moduleList->expects($this->exactly(2))->method('has')->will($this->returnValueMap([
            ['Module_Exists', true],
            ['Module_NotExists', false],
        ]));
        $this->assertTrue($this->_model->isEnabled('Module_Exists'));
        $this->assertFalse($this->_model->isEnabled('Module_NotExists'));
    }
}

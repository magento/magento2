<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanup;

    /**
     * @var Status
     */
    private $object;

    protected function setUp()
    {
        $this->loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->writer = $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false);
        $this->cleanup = $this->getMock('Magento\Framework\App\State\Cleanup', [], [], '', false);
        $this->object = new Status($this->loader, $this->moduleList, $this->writer, $this->cleanup);
    }

    public function testSetIsEnabled()
    {
        $modules = ['Module_Foo' => '', 'Module_Bar' => '', 'Module_Baz' => ''];
        $this->loader->expects($this->once())->method('load')->willReturn($modules);
        $this->moduleList->expects($this->at(0))->method('has')->with('Module_Foo')->willReturn(true);
        $this->moduleList->expects($this->at(1))->method('has')->with('Module_Bar')->willReturn(false);
        $this->moduleList->expects($this->at(2))->method('has')->with('Module_Baz')->willReturn(true);
        $constraint = new \PHPUnit_Framework_Constraint_IsInstanceOf(
            'Magento\Framework\Module\ModuleList\DeploymentConfig'
        );
        $this->writer->expects($this->once())->method('update')->with($constraint);
        $this->cleanup->expects($this->once())->method('clearCaches');
        $this->cleanup->expects($this->once())->method('clearCodeGeneratedFiles');
        $result = $this->object->setIsEnabled(true, ['Module_Foo', 'Module_Bar']);
        $this->assertEquals(['Module_Bar'], $result);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown module(s): 'Module_Baz'
     */
    public function testSetIsEnabledUnknown()
    {
        $modules = ['Module_Foo' => '', 'Module_Bar' => ''];
        $this->loader->expects($this->once())->method('load')->willReturn($modules);
        $this->object->setIsEnabled(true, ['Module_Baz']);
    }
}

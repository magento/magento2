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
    private $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $conflictChecker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dependencyChecker;

    /**
     * @var Status
     */
    private $object;

    protected function setUp()
    {
        $this->loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->reader = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->writer = $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false);
        $this->cleanup = $this->getMock('Magento\Framework\App\State\Cleanup', [], [], '', false);
        $this->conflictChecker = $this->getMock('Magento\Framework\Module\ConflictChecker', [], [], '', false);
        $this->dependencyChecker = $this->getMock('Magento\Framework\Module\DependencyChecker', [], [], '', false);
        $this->object = new Status(
            $this->loader,
            $this->moduleList,
            $this->reader,
            $this->writer,
            $this->cleanup,
            $this->conflictChecker,
            $this->dependencyChecker
        );
    }

    public function setUpCheckConstraints()
    {
        $modules = ['Module_Foo' => '', 'Module_Bar' => '', 'Module_Baz' => ''];
        $this->loader->expects($this->once())->method('load')->willReturn($modules);
        $fileIteratorMock = $this->getMock('Magento\Framework\Config\FileIterator', [], [], '', false);
        $fileIteratorMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue(['{}', '{}', '{}']));
        $this->reader->expects($this->once())
            ->method('getComposerJsonFiles')
            ->will($this->returnValue($fileIteratorMock));
    }
    public function testCheckConstraintsEnableAllowed()
    {
        $this->setUpCheckConstraints();
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->will($this->returnValue(['Module_Foo' => [], 'Module_Bar' => []]));
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->will($this->returnValue(['Module_Foo' => [], 'Module_Bar' => []]));
        $result = $this->object->checkConstraints(true, ['Module_Foo' => '', 'Module_Bar' => '']);
        $this->assertEquals([], $result);
    }

    public function testCheckConstraintsEnableNotAllowed()
    {
        $this->setUpCheckConstraints();
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->will($this->returnValue(['Module_Foo' => ['Module_Bar'], 'Module_Bar' => ['Module_Foo']]));
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->will($this->returnValue(
                [
                    'Module_Foo' => ['Module_Baz' => ['Module_Foo', 'Module_Baz(disabled)']],
                    'Module_Bar' => ['Module_Baz' => ['Module_Bar', 'Module_Baz(disabled)']],
                ]
            ));
        $result = $this->object->checkConstraints(true, ['Module_Foo' => '', 'Module_Bar' => '']);
        $expect = [
            'Cannot enable Module_Foo, depending on inactive modules:',
            "\tModule_Baz: Module_Foo->Module_Baz(disabled)",
            'Cannot enable Module_Bar, depending on inactive modules:',
            "\tModule_Baz: Module_Bar->Module_Baz(disabled)",
            'Cannot enable Module_Foo, conflicting active modules:',
            "\tModule_Bar",
            'Cannot enable Module_Bar, conflicting active modules:',
            "\tModule_Foo",
        ];
        $this->assertEquals($expect, $result);
    }

    public function testCheckConstraintsDisableAllowed()
    {
        $this->setUpCheckConstraints();
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->will($this->returnValue(['Module_Foo' => [], 'Module_Bar' => []]));
        $result = $this->object->checkConstraints(false, ['Module_Foo' => '', 'Module_Bar' => '']);
        $this->assertEquals([], $result);
    }

    public function testCheckConstraintsDisableNotAllowed()
    {
        $this->setUpCheckConstraints();
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->will($this->returnValue(
                [
                    'Module_Foo' => ['Module_Baz' => ['Module_Baz', 'Module_Foo']],
                    'Module_Bar' => ['Module_Baz' => ['Module_Baz', 'Module_Bar']],
                ]
            ));
        $result = $this->object->checkConstraints(false, ['Module_Foo' => '', 'Module_Bar' => '']);
        $expect = [
            'Cannot disable Module_Foo, active modules depending on it:',
            "\tModule_Baz: Module_Baz->Module_Foo",
            'Cannot disable Module_Bar, active modules depending on it:',
            "\tModule_Baz: Module_Baz->Module_Bar",
        ];
        $this->assertEquals($expect, $result);
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\Status;
use Magento\Framework\Config\File\ConfigFilePool;

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
        $this->loader = $this->getMock(\Magento\Framework\Module\ModuleList\Loader::class, [], [], '', false);
        $this->moduleList = $this->getMock(\Magento\Framework\Module\ModuleList::class, [], [], '', false);
        $this->writer = $this->getMock(\Magento\Framework\App\DeploymentConfig\Writer::class, [], [], '', false);
        $this->conflictChecker = $this->getMock(\Magento\Framework\Module\ConflictChecker::class, [], [], '', false);
        $this->dependencyChecker = $this->getMock(
            \Magento\Framework\Module\DependencyChecker::class,
            [],
            [],
            '',
            false
        );
        $this->object = new Status(
            $this->loader,
            $this->moduleList,
            $this->writer,
            $this->conflictChecker,
            $this->dependencyChecker
        );
    }

    public function testCheckConstraintsEnableAllowed()
    {
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->will($this->returnValue(['Module_Foo' => [], 'Module_Bar' => []]));
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->will($this->returnValue(['Module_Foo' => [], 'Module_Bar' => []]));
        $result = $this->object->checkConstraints(
            true,
            ['Module_Foo' => '', 'Module_Bar' => ''],
            ['Module_baz', 'Module_quz']
        );
        $this->assertEquals([], $result);
    }

    public function testCheckConstraintsEnableNotAllowed()
    {
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->will($this->returnValue(['Module_Foo' => ['Module_Bar'], 'Module_Bar' => ['Module_Foo']]));
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->will($this->returnValue(
                [
                    'Module_Foo' => ['Module_Baz' => ['Module_Foo', 'Module_Baz']],
                    'Module_Bar' => ['Module_Baz' => ['Module_Bar', 'Module_Baz']],
                ]
            ));
        $result = $this->object->checkConstraints(true, ['Module_Foo' => '', 'Module_Bar' => ''], [], false);
        $expect = [
            'Cannot enable Module_Foo because it depends on disabled modules:',
            "Module_Baz: Module_Foo->Module_Baz",
            'Cannot enable Module_Bar because it depends on disabled modules:',
            "Module_Baz: Module_Bar->Module_Baz",
            'Cannot enable Module_Foo because it conflicts with other modules:',
            "Module_Bar",
            'Cannot enable Module_Bar because it conflicts with other modules:',
            "Module_Foo",
        ];
        $this->assertEquals($expect, $result);
    }

    public function testCheckConstraintsEnableNotAllowedWithPrettyMsg()
    {
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->will($this->returnValue(['Module_Foo' => ['Module_Bar'], 'Module_Bar' => ['Module_Foo']]));
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->will($this->returnValue(
                [
                    'Module_Foo' => ['Module_Baz' => ['Module_Foo', 'Module_Baz']],
                    'Module_Bar' => ['Module_Baz' => ['Module_Bar', 'Module_Baz']],
                ]
            ));
        $result = $this->object->checkConstraints(true, ['Module_Foo' => '', 'Module_Bar' => ''], [], true);
        $expect = [
            'Cannot enable Module_Foo',
            'Cannot enable Module_Bar',
            'Cannot enable Module_Foo because it conflicts with other modules:',
            "Module_Bar",
            'Cannot enable Module_Bar because it conflicts with other modules:',
            "Module_Foo",
        ];
        $this->assertEquals($expect, $result);
    }

    public function testCheckConstraintsDisableAllowed()
    {
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->will($this->returnValue(['Module_Foo' => [], 'Module_Bar' => []]));
        $result = $this->object->checkConstraints(false, ['Module_Foo' => '', 'Module_Bar' => '']);
        $this->assertEquals([], $result);
    }

    public function testCheckConstraintsDisableNotAllowed()
    {
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
            'Cannot disable Module_Foo because modules depend on it:',
            "Module_Baz: Module_Baz->Module_Foo",
            'Cannot disable Module_Bar because modules depend on it:',
            "Module_Baz: Module_Baz->Module_Bar",
        ];
        $this->assertEquals($expect, $result);
    }

    public function testSetIsEnabled()
    {
        $modules = ['Module_Foo' => '', 'Module_Bar' => '', 'Module_Baz' => ''];
        $this->loader->expects($this->once())->method('load')->willReturn($modules);
        $this->moduleList->expects($this->at(0))->method('has')->with('Module_Foo')->willReturn(false);
        $this->moduleList->expects($this->at(1))->method('has')->with('Module_Bar')->willReturn(false);
        $this->moduleList->expects($this->at(2))->method('has')->with('Module_Baz')->willReturn(false);
        $expectedModules = ['Module_Foo' => 1, 'Module_Bar' => 1, 'Module_Baz' => 0];
        $this->writer->expects($this->once())->method('saveConfig')
            ->with([ConfigFilePool::APP_CONFIG => ['modules' => $expectedModules]]);
        $this->object->setIsEnabled(true, ['Module_Foo', 'Module_Bar']);
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

    /**
     * @dataProvider getModulesToChangeDataProvider
     * @param bool $firstEnabled
     * @param bool $secondEnabled
     * @param bool $thirdEnabled
     * @param bool $isEnabled
     * @param string[] $expected
     */
    public function testGetModulesToChange($firstEnabled, $secondEnabled, $thirdEnabled, $isEnabled, $expected)
    {
        $modules = ['Module_Foo' => '', 'Module_Bar' => '', 'Module_Baz' => ''];
        $this->loader->expects($this->once())->method('load')->willReturn($modules);
        $this->moduleList->expects($this->at(0))->method('has')->with('Module_Foo')->willReturn($firstEnabled);
        $this->moduleList->expects($this->at(1))->method('has')->with('Module_Bar')->willReturn($secondEnabled);
        $this->moduleList->expects($this->at(2))->method('has')->with('Module_Baz')->willReturn($thirdEnabled);
        $result = $this->object->getModulesToChange($isEnabled, ['Module_Foo', 'Module_Bar', 'Module_Baz']);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getModulesToChangeDataProvider()
    {
        return [
            [true, true, true, true, []],
            [true, true, false, true, ['Module_Baz']],
            [true, false, true, true, ['Module_Bar']],
            [true, false, false, true, ['Module_Bar', 'Module_Baz']],
            [false, false, false, true, ['Module_Foo', 'Module_Bar', 'Module_Baz']],
            [true, false, false, false, ['Module_Foo']],
            [false, true, false, false, ['Module_Bar']],
            [false, true, true, false, ['Module_Bar', 'Module_Baz']],
            [true, true, true, false, ['Module_Foo', 'Module_Bar', 'Module_Baz']],
        ];
    }
}

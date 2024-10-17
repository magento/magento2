<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\ConflictChecker;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Module\Status;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $loader;

    /**
     * @var MockObject
     */
    private $moduleList;

    /**
     * @var MockObject
     */
    private $writer;

    /**
     * @var MockObject
     */
    private $conflictChecker;

    /**
     * @var MockObject
     */
    private $dependencyChecker;

    /**
     * @var Status
     */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->loader = $this->createMock(Loader::class);
        $this->moduleList = $this->createMock(ModuleList::class);
        $this->writer = $this->createMock(Writer::class);
        $this->conflictChecker = $this->createMock(ConflictChecker::class);
        $this->dependencyChecker = $this->createMock(DependencyChecker::class);
        $this->object = new Status(
            $this->loader,
            $this->moduleList,
            $this->writer,
            $this->conflictChecker,
            $this->dependencyChecker
        );
    }

    /**
     * @return void
     */
    public function testCheckConstraintsEnableAllowed(): void
    {
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->willReturn(['Module_Foo' => [], 'Module_Bar' => []]);
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->willReturn(['Module_Foo' => [], 'Module_Bar' => []]);
        $result = $this->object->checkConstraints(
            true,
            ['Module_Foo' => '', 'Module_Bar' => ''],
            ['Module_baz', 'Module_quz']
        );
        $this->assertEquals([], $result);
    }

    /**
     * @return void
     */
    public function testCheckConstraintsEnableNotAllowed(): void
    {
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->willReturn(['Module_Foo' => ['Module_Bar'], 'Module_Bar' => ['Module_Foo']]);
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->willReturn([
                'Module_Foo' => ['Module_Baz' => ['Module_Foo', 'Module_Baz']],
                'Module_Bar' => ['Module_Baz' => ['Module_Bar', 'Module_Baz']]
            ]);
        $result = $this->object->checkConstraints(true, ['Module_Foo' => '', 'Module_Bar' => ''], [], false);
        $expect = [
            'Cannot enable Module_Foo because it depends on disabled modules:',
            "Module_Baz: Module_Foo->Module_Baz",
            'Cannot enable Module_Bar because it depends on disabled modules:',
            "Module_Baz: Module_Bar->Module_Baz",
            'Cannot enable Module_Foo because it conflicts with other modules:',
            "Module_Bar",
            'Cannot enable Module_Bar because it conflicts with other modules:',
            "Module_Foo"
        ];
        $this->assertEquals($expect, $result);
    }

    /**
     * @return void
     */
    public function testCheckConstraintsEnableNotAllowedWithPrettyMsg(): void
    {
        $this->conflictChecker->expects($this->once())
            ->method('checkConflictsWhenEnableModules')
            ->willReturn(['Module_Foo' => ['Module_Bar'], 'Module_Bar' => ['Module_Foo']]);
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenEnableModules')
            ->willReturn([
                'Module_Foo' => ['Module_Baz' => ['Module_Foo', 'Module_Baz']],
                'Module_Bar' => ['Module_Baz' => ['Module_Bar', 'Module_Baz']]
            ]);
        $result = $this->object->checkConstraints(true, ['Module_Foo' => '', 'Module_Bar' => ''], [], true);
        $expect = [
            'Cannot enable Module_Foo',
            'Cannot enable Module_Bar',
            'Cannot enable Module_Foo because it conflicts with other modules:',
            "Module_Bar",
            'Cannot enable Module_Bar because it conflicts with other modules:',
            "Module_Foo"
        ];
        $this->assertEquals($expect, $result);
    }

    /**
     * @return void
     */
    public function testCheckConstraintsDisableAllowed(): void
    {
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->willReturn(['Module_Foo' => [], 'Module_Bar' => []]);
        $result = $this->object->checkConstraints(false, ['Module_Foo' => '', 'Module_Bar' => '']);
        $this->assertEquals([], $result);
    }

    public function testCheckConstraintsDisableNotAllowed(): void
    {
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->willReturn([
                'Module_Foo' => ['Module_Baz' => ['Module_Baz', 'Module_Foo']],
                'Module_Bar' => ['Module_Baz' => ['Module_Baz', 'Module_Bar']]
            ]);
        $result = $this->object->checkConstraints(false, ['Module_Foo' => '', 'Module_Bar' => '']);
        $expect = [
            'Cannot disable Module_Foo because modules depend on it:',
            "Module_Baz: Module_Baz->Module_Foo",
            'Cannot disable Module_Bar because modules depend on it:',
            "Module_Baz: Module_Baz->Module_Bar"
        ];
        $this->assertEquals($expect, $result);
    }

    /**
     * @return void
     */
    public function testSetIsEnabled(): void
    {
        $modules = ['Module_Foo' => '', 'Module_Bar' => '', 'Module_Baz' => ''];
        $this->loader->expects($this->once())->method('load')->willReturn($modules);
        $this->moduleList
            ->method('has')
            ->willReturnCallback(
                function ($arg1) {
                    if ($arg1 == 'Module_Foo') {
                        return false;
                    } elseif ($arg1 == 'Module_Bar') {
                        return false;
                    } elseif ($arg1 == 'Module_Baz') {
                        return false;
                    }
                }
            );
        $expectedModules = ['Module_Foo' => 1, 'Module_Bar' => 1, 'Module_Baz' => 0];
        $this->writer->expects($this->once())->method('saveConfig')
            ->with([ConfigFilePool::APP_CONFIG => ['modules' => $expectedModules]]);
        $this->object->setIsEnabled(true, ['Module_Foo', 'Module_Bar']);
    }

    public function testSetIsEnabledUnknown(): void
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unknown module(s): \'Module_Baz\'');
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
     *
     * @return void
     */
    public function testGetModulesToChange(
        $firstEnabled,
        $secondEnabled,
        $thirdEnabled,
        $isEnabled,
        $expected
    ): void {
        $modules = ['Module_Foo' => '', 'Module_Bar' => '', 'Module_Baz' => ''];
        $this->loader->expects($this->once())->method('load')->willReturn($modules);
        $this->moduleList
            ->method('has')
            ->willReturnCallback(
                function ($arg1) use ($firstEnabled, $secondEnabled, $thirdEnabled) {
                    if ($arg1 == 'Module_Foo') {
                        return $firstEnabled;
                    } elseif ($arg1 == 'Module_Bar') {
                        return $secondEnabled;
                    } elseif ($arg1 == 'Module_Baz') {
                        return $thirdEnabled;
                    }
                }
            );
        $result = $this->object->getModulesToChange($isEnabled, ['Module_Foo', 'Module_Bar', 'Module_Baz']);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public static function getModulesToChangeDataProvider(): array
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
            [true, true, true, false, ['Module_Foo', 'Module_Bar', 'Module_Baz']]
        ];
    }
}

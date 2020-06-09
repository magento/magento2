<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\ConflictChecker;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use PHPUnit\Framework\TestCase;

class ConflictCheckerTest extends TestCase
{
    /**
     * @dataProvider checkConflictWhenEnableModuleDataProvider
     * @param array $conflictReturnMap
     * @param array $enabledModules
     * @param string $moduleName
     * @param array $expected
     */
    public function testCheckConflictsWhenEnableModules(
        $conflictReturnMap,
        $enabledModules,
        $moduleName,
        $expected
    ) {
        $moduleListMock = $this->createMock(ModuleList::class);
        $packageInfoMock = $this->createMock(PackageInfo::class);
        $moduleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn($enabledModules);
        $packageInfoMock->expects($this->any())
            ->method('getConflict')
            ->willReturnMap($conflictReturnMap);
        $packageInfoMock->expects($this->any())
            ->method('getVersion')
            ->willReturn('0.1');
        $packageInfoFactoryMock = $this->createMock(PackageInfoFactory::class);
        $packageInfoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($packageInfoMock);
        $conflictChecker = new ConflictChecker($moduleListMock, $packageInfoFactoryMock);
        $this->assertEquals($expected, $conflictChecker->checkConflictsWhenEnableModules($moduleName));
    }

    /**
     * @return array
     */
    public function checkConflictWhenEnableModuleDataProvider()
    {
        return [
            [
                [['Vendor_A', ['Vendor_B' => '0.1']], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A conflicts with current Vendor_B version 0.1 (version should not be 0.1)']]
            ],
            [
                [['Vendor_A', ['Vendor_B' => '0.1']], ['Vendor_B', []]],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_B', ['Vendor_A' => '0.1']], ['Vendor_A', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_B conflicts with current Vendor_A version 0.1 (version should not be 0.1)']]
            ],
            [
                [['Vendor_B', ['Vendor_A' => '0.1']], ['Vendor_A', []]],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_A', []], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_A', []], ['Vendor_B', []], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => [], 'Vendor_C' => []]
            ],
            [
                [['Vendor_A', ['Vendor_C' => '0.1']], ['Vendor_B', []], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                [
                    'Vendor_B' => [],
                    'Vendor_C' => ['Vendor_A conflicts with current Vendor_C version 0.1 (version should not be 0.1)']
                ]
            ],
            [
                [['Vendor_A', []], ['Vendor_B', ['Vendor_C' => '0.1']], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                [
                    'Vendor_B' => ['Vendor_B conflicts with current Vendor_C version 0.1 (version should not be 0.1)'],
                    'Vendor_C' => ['Vendor_B conflicts with current Vendor_C version 0.1 (version should not be 0.1)']
                ]
            ],
            [
                [['Vendor_A', ['Vendor_B' => '>=0.1']], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A conflicts with current Vendor_B version 0.1 (version should not be >=0.1)']]
            ],
            [
                [['Vendor_A', ['Vendor_B' => '~0.1']], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A conflicts with current Vendor_B version 0.1 (version should not be ~0.1)']]
            ],
        ];
    }

    public function testCheckConflictWhenEnableModuleDifferentVersion()
    {
        $moduleListMock = $this->createMock(ModuleList::class);
        $packageInfoMock = $this->createMock(PackageInfo::class);
        $moduleListMock->expects($this->any())
            ->method('getNames')
            ->willReturn(['Vendor_A', 'Vendor_B']);
        $packageInfoMock->expects($this->any())
            ->method('getConflict')
            ->willReturnMap([
                ['Vendor_A', []],
                ['Vendor_B', []],
                ['Vendor_C', ['Vendor_A' => '>=0.2,<0.3', 'Vendor_B' => '<0.4']]
            ]);
        $packageInfoMock->expects($this->any())
            ->method('getVersion')
            ->willReturnMap([['Vendor_A', '0.2'], ['Vendor_B', '0.4']]);
        $packageInfoFactoryMock = $this->createMock(PackageInfoFactory::class);
        $packageInfoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($packageInfoMock);
        $conflictChecker = new ConflictChecker($moduleListMock, $packageInfoFactoryMock);
        $this->assertEquals(
            ['Vendor_C' => ['Vendor_C conflicts with current Vendor_A version 0.2 (version should not be >=0.2,<0.3)']],
            $conflictChecker->checkConflictsWhenEnableModules(['Vendor_C'])
        );
    }

    public function testCheckConflictWhenEnableModuleDifferentVersionWithCurEnabledModules()
    {
        $moduleListMock = $this->createMock(ModuleList::class);
        $packageInfoMock = $this->createMock(PackageInfo::class);
        $packageInfoMock->expects($this->any())
            ->method('getConflict')
            ->willReturnMap([
                ['Vendor_A', []],
                ['Vendor_B', []],
                ['Vendor_C', ['Vendor_A' => '>=0.2,<0.3', 'Vendor_B' => '<0.4']]
            ]);
        $packageInfoMock->expects($this->any())
            ->method('getVersion')
            ->willReturnMap([['Vendor_A', '0.2'], ['Vendor_B', '0.4']]);
        $packageInfoFactoryMock = $this->createMock(PackageInfoFactory::class);
        $packageInfoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($packageInfoMock);
        $conflictChecker = new ConflictChecker($moduleListMock, $packageInfoFactoryMock);
        $this->assertEquals(
            ['Vendor_C' => ['Vendor_C conflicts with current Vendor_A version 0.2 (version should not be >=0.2,<0.3)']],
            $conflictChecker->checkConflictsWhenEnableModules(['Vendor_C'], ['Vendor_A', 'Vendor_B'])
        );
    }
}

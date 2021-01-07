<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DependencyCheckerTest extends TestCase
{
    /**
     * @var DependencyChecker|MockObject
     */
    private $checker;

    /**
     * @var PackageInfo|MockObject
     */
    private $packageInfoMock;

    /**
     * @var ModuleList|MockObject
     */
    private $listMock;

    /**
     * @var Loader|MockObject
     */
    private $loaderMock;

    protected function setUp(): void
    {
        $this->packageInfoMock = $this->createMock(PackageInfo::class);
        $requireMap = [
            ['A', ['B']],
            ['B', ['D', 'E']],
            ['C', ['E']],
            ['D', ['A']],
            ['E', []],
        ];
        $this->packageInfoMock
            ->expects($this->any())
            ->method('getRequire')
            ->willReturnMap($requireMap);

        $this->listMock = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $this->loaderMock = $this->createMock(\Magento\Framework\Module\ModuleList\Loader::class);
        $this->loaderMock
            ->expects($this->any())
            ->method('load')
            ->willReturn(['A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => []]);
    }

    public function testCheckDependenciesWhenDisableModules()
    {
        $this->listMock->expects($this->any())
            ->method('getNames')
            ->willReturn(['A', 'B', 'C', 'D', 'E']);
        $this->packageInfoMock->expects($this->atLeastOnce())
            ->method('getNonExistingDependencies')
            ->willReturn([]);
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoMock);

        $actual = $this->checker->checkDependenciesWhenDisableModules(['B', 'D']);
        $expected = ['B' => ['A' => ['A', 'B']], 'D' => ['A' => ['A', 'B', 'D']]];
        $this->assertEquals($expected, $actual);
    }

    public function testCheckDependenciesWhenDisableModulesWithCurEnabledModules()
    {
        $this->packageInfoMock->expects($this->atLeastOnce())
            ->method('getNonExistingDependencies')
            ->willReturn([]);
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoMock);

        $actual = $this->checker->checkDependenciesWhenDisableModules(['B', 'D'], ['C', 'D', 'E']);
        $expected = ['B' => [], 'D' => []];
        $this->assertEquals($expected, $actual);
    }

    public function testCheckDependenciesWhenEnableModules()
    {
        $this->listMock->expects($this->any())
            ->method('getNames')
            ->willReturn(['C']);
        $this->packageInfoMock->expects($this->atLeastOnce())
            ->method('getNonExistingDependencies')
            ->willReturn([]);
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoMock);
        $actual = $this->checker->checkDependenciesWhenEnableModules(['B', 'D']);
        $expected = [
            'B' => ['A' => ['B', 'D', 'A'], 'E' => ['B', 'E']],
            'D' => ['A' => ['D', 'A'], 'E' => ['D', 'A', 'B', 'E']],
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testCheckDependenciesWhenEnableModulesWithCurEnabledModules()
    {
        $this->packageInfoMock->expects($this->atLeastOnce())
            ->method('getNonExistingDependencies')
            ->willReturn([]);
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoMock);
        $actual = $this->checker->checkDependenciesWhenEnableModules(['B', 'D'], ['C']);
        $expected = [
            'B' => ['A' => ['B', 'D', 'A'], 'E' => ['B', 'E']],
            'D' => ['A' => ['D', 'A'], 'E' => ['D', 'A', 'B', 'E']],
        ];
        $this->assertEquals($expected, $actual);
    }
}

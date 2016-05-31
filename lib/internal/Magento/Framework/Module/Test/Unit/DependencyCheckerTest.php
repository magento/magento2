<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\DependencyChecker;

class DependencyCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\DependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checker;

    /**
     * @var \Magento\Framework\Module\PackageInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoMock;

    /**
     * @var \Magento\Framework\Module\PackageInfoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoFactoryMock;

    /**
     * @var \Magento\Framework\Module\ModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $listMock;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loaderMock;

    protected function setUp()
    {
        $this->packageInfoMock = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
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
            ->will($this->returnValueMap($requireMap));

        $this->packageInfoFactoryMock = $this->getMock(
            'Magento\Framework\Module\PackageInfoFactory',
            [],
            [],
            '',
            false
        );
        $this->packageInfoFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->packageInfoMock));

        $this->listMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->loaderMock = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->loaderMock
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValue(['A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => []]));
    }

    public function testCheckDependenciesWhenDisableModules()
    {
        $this->listMock->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['A', 'B', 'C', 'D', 'E']));
        $this->packageInfoMock->expects($this->atLeastOnce())
            ->method('getNonExistingDependencies')
            ->willReturn([]);
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoFactoryMock);

        $actual = $this->checker->checkDependenciesWhenDisableModules(['B', 'D']);
        $expected = ['B' => ['A' => ['A', 'B']], 'D' => ['A' => ['A', 'B', 'D']]];
        $this->assertEquals($expected, $actual);
    }

    public function testCheckDependenciesWhenDisableModulesWithCurEnabledModules()
    {
        $this->packageInfoMock->expects($this->atLeastOnce())
            ->method('getNonExistingDependencies')
            ->willReturn([]);
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoFactoryMock);

        $actual = $this->checker->checkDependenciesWhenDisableModules(['B', 'D'], ['C', 'D', 'E']);
        $expected = ['B' => [], 'D' => []];
        $this->assertEquals($expected, $actual);
    }

    public function testCheckDependenciesWhenEnableModules()
    {
        $this->listMock->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['C']));
        $this->packageInfoMock->expects($this->atLeastOnce())
            ->method('getNonExistingDependencies')
            ->willReturn([]);
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoFactoryMock);
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
        $this->checker = new DependencyChecker($this->listMock, $this->loaderMock, $this->packageInfoFactoryMock);
        $actual = $this->checker->checkDependenciesWhenEnableModules(['B', 'D'], ['C']);
        $expected = [
            'B' => ['A' => ['B', 'D', 'A'], 'E' => ['B', 'E']],
            'D' => ['A' => ['D', 'A'], 'E' => ['D', 'A', 'B', 'E']],
        ];
        $this->assertEquals($expected, $actual);
    }
}

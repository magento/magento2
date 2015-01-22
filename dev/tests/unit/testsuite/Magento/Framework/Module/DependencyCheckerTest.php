<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

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

    public function setUp()
    {
        $this->packageInfoMock = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $this->packageInfoMock
            ->expects($this->any())
            ->method('getAllModuleNames')
            ->will($this->returnValue(['A', 'B', 'C', 'D', 'E']));
        $requireMap = [
            ['A', ['vendor/module-B']],
            ['B', ['vendor/module-D', 'vendor/module-E']],
            ['C', ['vendor/module-E']],
            ['D', ['vendor/module-A']],
            ['E', []],
        ];
        $this->packageInfoMock
            ->expects($this->any())
            ->method('getRequire')
            ->will($this->returnValueMap($requireMap));

        $moduleNameMap = [
            ['vendor/module-A', 'A'],
            ['vendor/module-B', 'B'],
            ['vendor/module-C', 'C'],
            ['vendor/module-D', 'D'],
            ['vendor/module-E', 'E'],
        ];
        $this->packageInfoMock
            ->expects($this->any())
            ->method('getModuleName')
            ->will($this->returnValueMap($moduleNameMap));

        $this->checker = new DependencyChecker($this->packageInfoMock);
    }
    public function testCheckDependenciesWhenDisableModules()
    {
        $this->packageInfoMock
            ->expects($this->any())
            ->method('getEnabledModules')
            ->will($this->returnValue(['A', 'B', 'C', 'D', 'E']));

        $actual = $this->checker->checkDependenciesWhenDisableModules(['B', 'D']);
        $expected = ['B' => ['A' => ['A', 'B']], 'D' => ['A' => ['A', 'B', 'D']]];
        $this->assertEquals($expected, $actual);
    }

    public function testCheckDependenciesWhenEnableModules()
    {
        $this->packageInfoMock
            ->expects($this->any())
            ->method('getEnabledModules')
            ->will($this->returnValue(['C']));
        $actual = $this->checker->checkDependenciesWhenEnableModules(['B', 'D']);
        $expected = [
            'B' => ['A' => ['B', 'D', 'A'], 'E' => ['B', 'E']],
            'D' => ['A' => ['D', 'A'], 'E' => ['D', 'A', 'B', 'E']],
        ];
        $this->assertEquals($expected, $actual);
    }
}

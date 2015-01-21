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

    public function setUp()
    {
        $graph = new DependencyGraph(
            ['A', 'B', 'C', 'D', 'E'],
            [['A', 'B'], ['B', 'D'], ['B', 'E'], ['C', 'E'], ['D', 'A']]
        );
        $mapperMock = $this->getMock('Magento\Framework\Module\Mapper');
        $factoryMock = $this->getMock('Magento\Framework\Module\DependencyGraphFactory', [], [], '', false);
        $factoryMock->expects($this->once())->method('create')->will($this->returnValue($graph));
        $this->checker = new DependencyChecker($factoryMock, $mapperMock);
        $this->checker->setModulesData(['A' => '{}', 'B' => '{}', 'C' => '{}', 'D' => '{}', 'E' => '{}']);
    }
    public function testCheckDependenciesWhenDisableModules()
    {
        $this->checker->setEnabledModules(['A', 'B', 'C', 'D', 'E']);
        $actual = $this->checker->checkDependenciesWhenDisableModules(['B', 'D']);
        $expected = ['B' => ['A' => ['A', 'B']], 'D' => ['A' => ['A', 'B', 'D']]];
        $this->assertEquals($expected, $actual);
    }

    public function testCheckDependenciesWhenEnableModules()
    {
        $this->checker->setEnabledModules(['C']);
        $actual = $this->checker->checkDependenciesWhenEnableModules(['B', 'D']);
        $expected = [
            'B' => ['A' => ['B', 'D', 'A(disabled)'], 'E' => ['B', 'E(disabled)']],
            'D' => ['A' => ['D', 'A(disabled)'], 'E' => ['D', 'A(disabled)', 'B', 'E(disabled)']],
        ];
        $this->assertEquals($expected, $actual);
    }
}

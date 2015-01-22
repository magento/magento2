<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class PackageInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\ModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loader;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    public function setUp()
    {
        $this->moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->reader = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->moduleList->expects($this->any())->method('getNames')->will($this->returnValue(['A', 'B', 'C']));
        $this->loader->expects($this->any())
            ->method('load')
            ->will($this->returnValue(['A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => []]));

        $composerData = [
            'A' => '{"name":"a", "require":{"b":"0.1"}, "conflict":{"c":"0.1"}}',
            'B' => '{"name":"b", "require":{"d":"0.1"}}',
            'C' => '{"name":"c", "require":{"e":"0.1"}}',
            'D' => '{"name":"d", "conflict":{"c":"0.1"}}',
            'E' => '{"name":"e"}',
        ];
        $fileIteratorMock = $this->getMock('Magento\Framework\Config\FileIterator', [], [], '', false);
        $fileIteratorMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($composerData));
        $this->reader->expects($this->any())
            ->method('getComposerJsonFiles')
            ->will($this->returnValue($fileIteratorMock));

        $this->packageInfo = new PackageInfo($this->moduleList, $this->loader, $this->reader);
    }

    public function testGetAllModuleNames()
    {
        $this->assertEquals(['A', 'B', 'C', 'D', 'E'], $this->packageInfo->getAllModuleNames());
    }

    public function testGetEnabledModules()
    {
        $this->assertEquals(['A', 'B', 'C'], $this->packageInfo->getEnabledModules());
    }

    public function testGetPackageName()
    {
        $this->assertEquals('a', $this->packageInfo->getPackageName('A'));
        $this->assertEquals('b', $this->packageInfo->getPackageName('B'));
        $this->assertEquals('c', $this->packageInfo->getPackageName('C'));
        $this->assertEquals('d', $this->packageInfo->getPackageName('D'));
        $this->assertEquals('e', $this->packageInfo->getPackageName('E'));
    }

    public function testGetModuleName()
    {
        $this->assertEquals('A', $this->packageInfo->getModuleName('a'));
        $this->assertEquals('B', $this->packageInfo->getModuleName('b'));
        $this->assertEquals('C', $this->packageInfo->getModuleName('c'));
        $this->assertEquals('D', $this->packageInfo->getModuleName('d'));
        $this->assertEquals('E', $this->packageInfo->getModuleName('e'));
    }

    public function testGetRequire()
    {
        $this->assertEquals(['b'], $this->packageInfo->getRequire('A'));
        $this->assertEquals(['d'], $this->packageInfo->getRequire('B'));
        $this->assertEquals(['e'], $this->packageInfo->getRequire('C'));
        $this->assertEquals([], $this->packageInfo->getRequire('D'));
        $this->assertEquals([], $this->packageInfo->getRequire('E'));
    }

    public function testGetConflict()
    {
        $this->assertEquals(['c'], $this->packageInfo->getConflict('A'));
        $this->assertEquals([], $this->packageInfo->getConflict('B'));
        $this->assertEquals([], $this->packageInfo->getConflict('C'));
        $this->assertEquals(['c'], $this->packageInfo->getConflict('D'));
        $this->assertEquals([], $this->packageInfo->getConflict('E'));
    }
}

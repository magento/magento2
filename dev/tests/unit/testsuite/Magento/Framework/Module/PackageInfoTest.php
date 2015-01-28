<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class PackageInfoTest extends \PHPUnit_Framework_TestCase
{
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
        $this->loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->reader = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->loader->expects($this->once())
            ->method('load')
            ->will($this->returnValue(['A' => [], 'B' => [], 'C' => [], 'D' => [], 'E' => []]));

        $composerData = [
            'A' => '{"name":"a", "require":{"b":"0.1"}, "conflict":{"c":"0.1"}, "version":"0.1"}',
            'B' => '{"name":"b", "require":{"d":"0.1"}, "version":"0.1"}',
            'C' => '{"name":"c", "require":{"e":"0.1"}, "version":"0.1"}',
            'D' => '{"name":"d", "conflict":{"c":"0.1"}, "version":"0.1"}',
            'E' => '{"name":"e", "version":"0.1"}',
        ];
        $fileIteratorMock = $this->getMock('Magento\Framework\Config\FileIterator', [], [], '', false);
        $fileIteratorMock->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($composerData));
        $this->reader->expects($this->once())
            ->method('getComposerJsonFiles')
            ->will($this->returnValue($fileIteratorMock));

        $this->packageInfo = new PackageInfo($this->loader, $this->reader);
    }

    public function testGetModuleName()
    {
        $this->assertEquals('A', $this->packageInfo->getModuleName('a'));
        $this->assertEquals('B', $this->packageInfo->getModuleName('b'));
        $this->assertEquals('C', $this->packageInfo->getModuleName('c'));
        $this->assertEquals('D', $this->packageInfo->getModuleName('d'));
        $this->assertEquals('E', $this->packageInfo->getModuleName('e'));
    }

    public function testGetRequireReturnModuleName()
    {
        $this->assertEquals(['B'], $this->packageInfo->getRequire('A'));
        $this->assertEquals(['D'], $this->packageInfo->getRequire('B'));
        $this->assertEquals(['E'], $this->packageInfo->getRequire('C'));
        $this->assertEquals([], $this->packageInfo->getRequire('D'));
        $this->assertEquals([], $this->packageInfo->getRequire('E'));
    }

    public function testGetConflictReturnModuleName()
    {
        $this->assertEquals(['C' => '0.1'], $this->packageInfo->getConflict('A'));
        $this->assertEquals([], $this->packageInfo->getConflict('B'));
        $this->assertEquals([], $this->packageInfo->getConflict('C'));
        $this->assertEquals(['C' => '0.1'], $this->packageInfo->getConflict('D'));
        $this->assertEquals([], $this->packageInfo->getConflict('E'));
    }

    public function testGetRequireReturnPackageName()
    {
        $this->assertEquals(['b'], $this->packageInfo->getRequire('A', false));
        $this->assertEquals(['d'], $this->packageInfo->getRequire('B', false));
        $this->assertEquals(['e'], $this->packageInfo->getRequire('C', false));
        $this->assertEquals([], $this->packageInfo->getRequire('D', false));
        $this->assertEquals([], $this->packageInfo->getRequire('E', false));
    }

    public function testGetConflictReturnPackageName()
    {
        $this->assertEquals(['c' => '0.1'], $this->packageInfo->getConflict('A', false));
        $this->assertEquals([], $this->packageInfo->getConflict('B', false));
        $this->assertEquals([], $this->packageInfo->getConflict('C', false));
        $this->assertEquals(['c' => '0.1'], $this->packageInfo->getConflict('D', false));
        $this->assertEquals([], $this->packageInfo->getConflict('E', false));
    }
}

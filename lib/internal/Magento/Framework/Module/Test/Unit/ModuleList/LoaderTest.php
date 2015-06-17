<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\Test\Unit\ModuleList;

use \Magento\Framework\Module\ModuleList\Loader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Xml\Parser;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * A sample empty XML
     *
     * @var string
     */
    private static $sampleXml = '<?xml version="1.0"?><test></test>';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $parser;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $driver;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    private $loader;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->dir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MODULES)
            ->willReturn($this->dir);
        $this->converter = $this->getMock('Magento\Framework\Module\Declaration\Converter\Dom', [], [], '', false);
        $this->parser = $this->getMock('Magento\Framework\Xml\Parser', [], [], '', false);
        $this->parser->expects($this->once())->method('initErrorHandler');
        $this->registry = $this->getMock('Magento\Framework\Module\ModuleRegistryInterface', [], [], '', false, false);
        $this->driver = $this->getMock('Magento\Framework\Filesystem\DriverInterface', [], [], '', false, false);
        $this->loader = new Loader($this->filesystem, $this->converter, $this->parser, $this->registry, $this->driver);
    }

    public function testLoad()
    {
        $fixtures = [
            'a' => ['name' => 'a', 'sequence' => []],    // a is on its own
            'b' => ['name' => 'b', 'sequence' => ['d']], // b is after d
            'c' => ['name' => 'c', 'sequence' => ['e']], // c is after e
            'd' => ['name' => 'd', 'sequence' => ['c']], // d is after c
            'e' => ['name' => 'e', 'sequence' => ['a']], // e is after a
            // so expected sequence is a -> e -> c -> d -> b
        ];
        $this->dir->expects($this->once())->method('search')->willReturn(['a', 'b', 'c']);
        $this->registry->expects($this->once())->method('getModulePaths')->willReturn(['/path/to/d', '/path/to/e']);
        $this->dir->expects($this->exactly(3))->method('readFile')->will($this->returnValueMap([
            ['a', null, null, self::$sampleXml],
            ['b', null, null, self::$sampleXml],
            ['c', null, null, self::$sampleXml],
        ]));
        $this->driver->expects($this->exactly(2))->method('fileGetContents')->will($this->returnValueMap([
            ['/path/to/d/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/e/etc/module.xml', null, null, self::$sampleXml],
        ]));
        $index = 0;
        foreach ($fixtures as $name => $fixture) {
            $this->converter->expects($this->at($index++))->method('convert')->willReturn([$name => $fixture]);
        }
        $this->parser->expects($this->atLeastOnce())->method('loadXML')
            ->with(self::$sampleXml);
        $this->parser->expects($this->atLeastOnce())->method('getDom');
        $result = $this->loader->load();
        $this->assertSame(['a', 'e', 'c', 'd', 'b'], array_keys($result));
        foreach ($fixtures as $name => $fixture) {
            $this->assertSame($fixture, $result[$name]);
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Circular sequence reference from 'b' to 'a'
     */
    public function testLoadCircular()
    {
        $fixture = [
            'a' => ['name' => 'a', 'sequence' => ['b']],
            'b' => ['name' => 'b', 'sequence' => ['a']],
        ];
        $this->dir->expects($this->once())->method('search')->willReturn(['a', 'b']);
        $this->dir->expects($this->exactly(2))->method('readFile')->will($this->returnValueMap([
            ['a', null, null, self::$sampleXml],
            ['b', null, null, self::$sampleXml],
        ]));
        $this->converter->expects($this->at(0))->method('convert')->willReturn(['a' => $fixture['a']]);
        $this->converter->expects($this->at(1))->method('convert')->willReturn(['b' => $fixture['b']]);
        $this->registry->expects($this->once())->method('getModulePaths')->willReturn([]);
        $this->loader->load();
    }
}

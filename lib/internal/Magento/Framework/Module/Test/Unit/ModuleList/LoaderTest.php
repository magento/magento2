<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\Test\Unit\ModuleList;

use \Magento\Framework\Module\ModuleList\Loader;

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

    /**
     * @var array
     */
    private $loadFixture;

    protected function setUp()
    {
        $this->converter = $this->getMock(
            \Magento\Framework\Module\Declaration\Converter\Dom::class,
            [],
            [],
            '',
            false
        );
        $this->parser = $this->getMock(\Magento\Framework\Xml\Parser::class, [], [], '', false);
        $this->parser->expects($this->once())->method('initErrorHandler');
        $this->registry = $this->getMock(
            \Magento\Framework\Component\ComponentRegistrarInterface::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->driver = $this->getMock(\Magento\Framework\Filesystem\DriverInterface::class, [], [], '', false, false);
        $this->loader = new Loader($this->converter, $this->parser, $this->registry, $this->driver);
    }

    /**
     * @param $paths
     * @dataProvider testLoadDataProvider
     */
    public function testLoad($paths)
    {
        $this->registry->expects($this->once())
            ->method('getPaths')
            ->willReturn($paths);
        $this->loadFixture = [
            'a' => ['name' => 'a', 'sequence' => []],    // a is on its own
            'b' => ['name' => 'b', 'sequence' => ['d']], // b is after d
            'c' => ['name' => 'c', 'sequence' => ['e']], // c is after e
            'd' => ['name' => 'd', 'sequence' => ['c']], // d is after c
            'e' => ['name' => 'e', 'sequence' => ['a']], // e is after a
            // so expected sequence is a -> e -> c -> d -> b
        ];
        $this->driver->expects($this->exactly(5))->method('fileGetContents')->will($this->returnValueMap([
            ['/path/to/a/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/b/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/c/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/d/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/e/etc/module.xml', null, null, self::$sampleXml],
        ]));
        $index = 0;
        foreach ($this->loadFixture as $name => $fixture) {
            $this->converter->expects($this->at($index++))->method('convert')->willReturn([$name => $fixture]);
        }
        $this->parser->expects($this->atLeastOnce())->method('loadXML')
            ->with(self::$sampleXml);
        $this->parser->expects($this->atLeastOnce())->method('getDom');
        $result = $this->loader->load();
        $this->assertSame(['a', 'e', 'c', 'd', 'b'], array_keys($result));
        foreach ($this->loadFixture as $name => $fixture) {
            $this->assertSame($fixture, $result[$name]);
        }
    }

    /**
     * @return array
     */
    public function testLoadDataProvider()
    {
        return [
            'Ordered modules list returned by registrar' => [[
                '/path/to/a', '/path/to/b', '/path/to/c', '/path/to/d', '/path/to/e'
            ]],
            'UnOrdered modules list returned by registrar' => [[
                '/path/to/b', '/path/to/a', '/path/to/c', '/path/to/e', '/path/to/d'
            ]],
        ];
    }

    public function testLoadExclude()
    {
        $fixture = [
            'a' => ['name' => 'a', 'sequence' => []],    // a is on its own
            'b' => ['name' => 'b', 'sequence' => ['c']], // b is after c
            'c' => ['name' => 'c', 'sequence' => ['a']], // c is after a
            'd' => ['name' => 'd', 'sequence' => ['a']], // d is after a
            // exclude d, so expected sequence is a -> c -> b
        ];
        $this->registry->expects($this->once())
            ->method('getPaths')
            ->willReturn(['/path/to/a', '/path/to/b', '/path/to/c', '/path/to/d']);
        $this->driver->expects($this->exactly(4))->method('fileGetContents')->will($this->returnValueMap([
            ['/path/to/a/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/b/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/c/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/d/etc/module.xml', null, null, self::$sampleXml],
        ]));
        $this->converter->expects($this->at(0))->method('convert')->willReturn(['a' => $fixture['a']]);
        $this->converter->expects($this->at(1))->method('convert')->willReturn(['b' => $fixture['b']]);
        $this->converter->expects($this->at(2))->method('convert')->willReturn(['c' => $fixture['c']]);
        $this->converter->expects($this->at(3))->method('convert')->willReturn(['d' => $fixture['d']]);
        $this->parser->expects($this->atLeastOnce())->method('loadXML');
        $this->parser->expects($this->atLeastOnce())->method('getDom');
        $result = $this->loader->load(['d']);
        $this->assertSame(['a', 'c', 'b'], array_keys($result));
        $this->assertSame($fixture['a'], $result['a']);
        $this->assertSame($fixture['b'], $result['b']);
        $this->assertSame($fixture['c'], $result['c']);
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
        $this->converter->expects($this->at(0))->method('convert')->willReturn(['a' => $fixture['a']]);
        $this->converter->expects($this->at(1))->method('convert')->willReturn(['b' => $fixture['b']]);
        $this->registry->expects($this->once())->method('getPaths')->willReturn(['/path/to/a', '/path/to/b']);
        $this->driver->expects($this->exactly(2))->method('fileGetContents')->will($this->returnValueMap([
            ['/path/to/a/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/b/etc/module.xml', null, null, self::$sampleXml],
        ]));
        $this->loader->load();
    }
}

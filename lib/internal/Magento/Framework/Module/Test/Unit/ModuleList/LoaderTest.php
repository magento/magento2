<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Module\Test\Unit\ModuleList;

use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\Declaration\Converter\Dom;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Xml\Parser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    /**
     * A sample empty XML
     *
     * @var string
     */
    private static $sampleXml = '<?xml version="1.0"?><test></test>';

    /**
     * @var MockObject
     */
    private $converter;

    /**
     * @var MockObject
     */
    private $parser;

    /**
     * @var MockObject
     */
    private $registry;

    /**
     * @var MockObject
     */
    private $driver;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var array
     */
    private $loadFixture;

    protected function setUp(): void
    {
        $this->converter = $this->createMock(Dom::class);
        $this->parser = $this->createMock(Parser::class);
        $this->parser->expects($this->once())->method('initErrorHandler');
        $this->registry = $this->getMockForAbstractClass(ComponentRegistrarInterface::class);
        $this->driver = $this->getMockForAbstractClass(DriverInterface::class);
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
        $this->driver->expects($this->exactly(5))->method('fileGetContents')->willReturnMap([
            ['/path/to/a/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/b/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/c/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/d/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/e/etc/module.xml', null, null, self::$sampleXml],
        ]);
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
        $this->driver->expects($this->exactly(4))->method('fileGetContents')->willReturnMap([
            ['/path/to/a/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/b/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/c/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/d/etc/module.xml', null, null, self::$sampleXml],
        ]);
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

    public function testLoadCircular()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Circular sequence reference from \'b\' to \'a\'');
        $fixture = [
            'a' => ['name' => 'a', 'sequence' => ['b']],
            'b' => ['name' => 'b', 'sequence' => ['a']],
        ];
        $this->converter->expects($this->at(0))->method('convert')->willReturn(['a' => $fixture['a']]);
        $this->converter->expects($this->at(1))->method('convert')->willReturn(['b' => $fixture['b']]);
        $this->registry->expects($this->once())->method('getPaths')->willReturn(['/path/to/a', '/path/to/b']);
        $this->driver->expects($this->exactly(2))->method('fileGetContents')->willReturnMap([
            ['/path/to/a/etc/module.xml', null, null, self::$sampleXml],
            ['/path/to/b/etc/module.xml', null, null, self::$sampleXml],
        ]);
        $this->loader->load();
    }

    /**
     * @throws LocalizedException
     */
    public function testLoadPrearranged(): void
    {
        $fixtures = [
            'Foo_Bar' => ['name' => 'Foo_Bar', 'sequence' => ['Magento_Store']],
            'Magento_Directory' => ['name' => 'Magento_Directory', 'sequence' => ['Magento_Store']],
            'Magento_Store' => ['name' => 'Magento_Store', 'sequence' => []],
            'Magento_Theme' => ['name' => 'Magento_Theme', 'sequence' => ['Magento_Store', 'Magento_Directory']],
            'Test_HelloWorld' => ['name' => 'Test_HelloWorld', 'sequence' => ['Magento_Theme']]
        ];

        $index = 0;
        foreach ($fixtures as $name => $fixture) {
            $this->converter->expects($this->at($index++))->method('convert')->willReturn([$name => $fixture]);
        }

        $this->registry->expects($this->once())
            ->method('getPaths')
            ->willReturn([
                '/path/to/Foo_Bar',
                '/path/to/Magento_Directory',
                '/path/to/Magento_Store',
                '/path/to/Magento_Theme',
                '/path/to/Test_HelloWorld'
            ]);

        $this->driver->expects($this->exactly(5))
            ->method('fileGetContents')
            ->willReturnMap([
                ['/path/to/Foo_Bar/etc/module.xml', null, null, self::$sampleXml],
                ['/path/to/Magento_Directory/etc/module.xml', null, null, self::$sampleXml],
                ['/path/to/Magento_Store/etc/module.xml', null, null, self::$sampleXml],
                ['/path/to/Magento_Theme/etc/module.xml', null, null, self::$sampleXml],
                ['/path/to/Test_HelloWorld/etc/module.xml', null, null, self::$sampleXml],
            ]);

        // Load the full module list information
        $result = $this->loader->load();

        $this->assertSame(
            ['Magento_Store', 'Magento_Directory', 'Magento_Theme', 'Foo_Bar', 'Test_HelloWorld'],
            array_keys($result)
        );

        foreach ($fixtures as $name => $fixture) {
            $this->assertSame($fixture, $result[$name]);
        }
    }
}

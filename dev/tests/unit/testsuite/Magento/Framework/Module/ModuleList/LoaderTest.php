<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

use Magento\Framework\App\Filesystem\DirectoryList;

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

    protected function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->dir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MODULES)
            ->willReturn($this->dir);
        $this->converter = $this->getMock('Magento\Framework\Module\Declaration\Converter\Dom', [], [], '', false);
    }

    public function testLoad()
    {
        $fixture = [
            'a' => ['name' => 'a', 'sequence' => []],    // a is on its own
            'b' => ['name' => 'b', 'sequence' => ['c']], // b is after c
            'c' => ['name' => 'c', 'sequence' => ['a']], // c is after a
            // so expected sequence is a -> c -> b
        ];
        $this->dir->expects($this->once())->method('search')->willReturn(['a', 'b', 'c']);
        $this->dir->expects($this->exactly(3))->method('readFile')->will($this->returnValueMap([
            ['a', null, null, self::$sampleXml],
            ['b', null, null, self::$sampleXml],
            ['c', null, null, self::$sampleXml],
        ]));
        $dom = new \PHPUnit_Framework_Constraint_IsInstanceOf('DOMDocument');
        $this->converter->expects($this->at(0))->method('convert')->with($dom)->willReturn(['a' => $fixture['a']]);
        $this->converter->expects($this->at(1))->method('convert')->with($dom)->willReturn(['b' => $fixture['b']]);
        $this->converter->expects($this->at(2))->method('convert')->with($dom)->willReturn(['c' => $fixture['c']]);
        $object = new Loader($this->filesystem, $this->converter);
        $result = $object->load();
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
        $this->dir->expects($this->once())->method('search')->willReturn(['a', 'b']);
        $this->dir->expects($this->exactly(2))->method('readFile')->will($this->returnValueMap([
            ['a', null, null, self::$sampleXml],
            ['b', null, null, self::$sampleXml],
        ]));
        $dom = new \PHPUnit_Framework_Constraint_IsInstanceOf('DOMDocument');
        $this->converter->expects($this->at(0))->method('convert')->with($dom)->willReturn(['a' => $fixture['a']]);
        $this->converter->expects($this->at(1))->method('convert')->with($dom)->willReturn(['b' => $fixture['b']]);
        $object = new Loader($this->filesystem, $this->converter);
        $object->load();
    }
}

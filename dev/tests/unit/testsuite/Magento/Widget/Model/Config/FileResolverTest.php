<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileResolver
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $themesDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->themesDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($this->themesDir);
        $this->moduleReader = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->factory = $this->getMock('Magento\Framework\Config\FileIteratorFactory', [], [], '', false);
        $this->object = new FileResolver($filesystem, $this->moduleReader, $this->factory);
    }

    public function testGetGlobal()
    {
        $expected = new \StdClass();
        $this->moduleReader
            ->expects($this->once())
            ->method('getConfigurationFiles')
            ->with('file')
            ->willReturn($expected);
        $this->assertSame($expected, $this->object->get('file', 'global'));
    }

    public function testGetDesign()
    {
        $expected = new \StdClass();
        $this->themesDir->expects($this->once())->method('search')->with('/*/*/etc/file')->willReturn('test');
        $this->factory->expects($this->once())->method('create')->with($this->themesDir, 'test')->willReturn($expected);
        $this->assertSame($expected, $this->object->get('file', 'design'));
    }

    public function testGetDefault()
    {
        $expected = new \StdClass();
        $this->factory->expects($this->once())->method('create')->with($this->themesDir, [])->willReturn($expected);
        $this->assertSame($expected, $this->object->get('file', 'unknown'));
    }
}

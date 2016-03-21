<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component\Test\Unit;

use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\DriverPool;

class DirSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dir;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readFactory;

    /**
     * @var DirSearch
     */
    private $object;

    protected function setUp()
    {
        $this->registrar = $this->getMockForAbstractClass('\Magento\Framework\Component\ComponentRegistrarInterface');
        $this->readFactory = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $this->dir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->dir->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnArgument(0);
        $this->object = new DirSearch($this->registrar, $this->readFactory);
    }

    public function testCollectFilesNothingFound()
    {
        $componentType = 'component_type';
        $this->registrar->expects($this->exactly(2))
            ->method('getPaths')
            ->with($componentType)
            ->willReturn([]);
        $this->readFactory->expects($this->never())
            ->method('create');
        $this->assertSame([], $this->object->collectFiles($componentType, '*/file.xml'));
        $this->assertSame([], $this->object->collectFilesWithContext($componentType, '*/file.xml'));
    }

    public function testCollectFiles()
    {
        $componentType = 'component_type';
        $componentPaths = ['component1' => 'path1', 'component2' => 'path2'];
        $pattern = '*/file.xml';
        $this->registrar->expects($this->once())
            ->method('getPaths')
            ->with($componentType)
            ->willReturn($componentPaths);
        $this->readFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                ['path1', DriverPool::FILE, $this->dir],
                ['path2', DriverPool::FILE, $this->dir],
            ]);
        $this->dir->method('search')
            ->with($pattern)
            ->willReturnOnConsecutiveCalls(['one/file.xml'], ['two/file.xml']);
        $expected = ['one/file.xml', 'two/file.xml'];
        $this->assertSame($expected, $this->object->collectFiles($componentType, $pattern));
    }

    public function testCollectFilesWithContext()
    {
        $componentType = 'component_type';
        $componentPaths = ['component1' => 'path1', 'component2' => 'path2'];
        $pattern = '*/file.xml';
        $this->registrar->expects($this->once())
            ->method('getPaths')
            ->with($componentType)
            ->willReturn($componentPaths);
        $this->readFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                ['path1', DriverPool::FILE, $this->dir],
                ['path2', DriverPool::FILE, $this->dir],
            ]);
        $this->dir->method('search')
            ->with($pattern)
            ->willReturnOnConsecutiveCalls(['one/file.xml'], ['two/file.xml']);
        $actualFiles = $this->object->collectFilesWithContext($componentType, $pattern);
        $this->assertNotEmpty($actualFiles);
        /** @var \Magento\Framework\Component\ComponentFile $file */
        foreach ($actualFiles as $file) {
            $this->assertInstanceOf('\Magento\Framework\Component\ComponentFile', $file);
            $this->assertSame($componentType, $file->getComponentType());
        }
        $this->assertCount(2, $actualFiles);
        $this->assertSame('component1', $actualFiles[0]->getComponentName());
        $this->assertSame('one/file.xml', $actualFiles[0]->getFullPath());
        $this->assertSame('component2', $actualFiles[1]->getComponentName());
        $this->assertSame('two/file.xml', $actualFiles[1]->getFullPath());
    }
}
